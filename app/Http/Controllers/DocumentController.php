<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use setasign\Fpdi\Fpdi;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $documents = Document::where('user_id', Auth::id())
                ->latest()
                ->get();

            if ($documents->isEmpty()) {
                return response()->json([], 200);
            }

            return response()->json($documents, 200);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch documents: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to load documents. Please try again.'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'document' => 'required|file|mimes:pdf|max:10240',
            ]);

            if (!$request->hasFile('document')) {
                return response()->json([
                    'message' => 'No document file was uploaded'
                ], 422);
            }

            $file = $request->file('document');
            
            if (!$file->isValid()) {
                return response()->json([
                    'message' => 'File upload failed. Please try again.'
                ], 422);
            }

            $path = $file->store('documents', 'public');

            if (!$path) {
                return response()->json([
                    'message' => 'Failed to store the file. Please try again.'
                ], 500);
            }

            $document = Document::create([
                'name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'size' => $file->getSize(),
                'user_id' => Auth::id(),
                'status' => 'pending'
            ]);

            return response()->json($document, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Document upload failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to upload document. Please try again.'
            ], 500);
        }
    }

    /**
     * Display the specified document.
     */
    public function show(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $path = Storage::disk('public')->path($document->file_path);
            
            if (!file_exists($path)) {
                return response()->json(['message' => 'Document not found'], 404);
            }

            return response()->file($path);
        } catch (\Exception $e) {
            \Log::error('Error viewing document: ' . $e->getMessage());
            return response()->json(['message' => 'Error viewing document'], 500);
        }
    }

    /**
     * Sign the specified document.
     */
    public function sign(Request $request, Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $request->validate([
                'signature' => 'required|string',
                'type' => 'required|in:draw,type'
            ]);

            // Get the PDF path
            $pdfPath = Storage::disk('public')->path($document->file_path);
            
            if (!file_exists($pdfPath)) {
                return response()->json(['message' => 'Document not found'], 404);
            }

            // Create signature image from base64 data or text
            $signature = $request->input('signature');
            $signatureType = $request->input('type');
            
            if ($signatureType === 'draw') {
                // Convert base64 signature to image
                $signatureImage = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signature));
                $signaturePath = Storage::disk('public')->path('signatures/' . uniqid() . '.png');
                file_put_contents($signaturePath, $signatureImage);
            } else {
                // Create image from text
                $fontSize = 24;
                $img = imagecreatetruecolor(500, 100);
                $bgColor = imagecolorallocate($img, 255, 255, 255);
                $textColor = imagecolorallocate($img, 0, 0, 0);
                imagefill($img, 0, 0, $bgColor);
                imagettftext($img, $fontSize, 0, 10, 50, $textColor, storage_path('app/fonts/signature.ttf'), $signature);
                $signaturePath = Storage::disk('public')->path('signatures/' . uniqid() . '.png');
                imagepng($img, $signaturePath);
                imagedestroy($img);
            }

            // Create a temporary copy of the PDF
            $tempPath = Storage::disk('public')->path('temp/' . uniqid() . '.pdf');
            copy($pdfPath, $tempPath);

            // Add signature to PDF using FPDI
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($tempPath);
            
            // Add signature to the last page
            $pdf->AddPage();
            $tplIdx = $pdf->importPage($pageCount);
            $pdf->useTemplate($tplIdx);
            
            // Add signature image
            $pdf->Image($signaturePath, 30, $pdf->GetY() - 50, 50);
            
            // Add signature date
            $pdf->SetFont('Helvetica', '', 10);
            $pdf->SetXY(30, $pdf->GetY() - 20);
            $pdf->Cell(0, 10, 'Signed on: ' . now()->format('Y-m-d H:i:s'), 0, 1);
            
            // Save the signed PDF
            $signedPdfPath = Storage::disk('public')->path($document->file_path);
            $pdf->Output($signedPdfPath, 'F');

            // Update document status
            $document->update([
                'status' => 'signed',
                'signed_at' => now()
            ]);

            // Clean up temporary files
            @unlink($tempPath);
            @unlink($signaturePath);

            return response()->json([
                'message' => 'Document signed successfully',
                'document' => $document
            ]);
        } catch (\Exception $e) {
            \Log::error('Error signing document: ' . $e->getMessage());
            return response()->json(['message' => 'Error signing document: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Download the specified document.
     */
    public function download(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $path = Storage::disk('public')->path($document->file_path);
            
            if (!file_exists($path)) {
                return response()->json(['message' => 'Document not found'], 404);
            }

            return response()->download($path, $document->name);
        } catch (\Exception $e) {
            \Log::error('Error downloading document: ' . $e->getMessage());
            return response()->json(['message' => 'Error downloading document'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
