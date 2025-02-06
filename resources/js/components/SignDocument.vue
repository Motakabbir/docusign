<template>
  <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" v-if="show">
    <div class="fixed inset-0 z-10 overflow-y-auto">
      <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl sm:p-6">
          <div class="absolute right-0 top-0 pr-4 pt-4">
            <button
              type="button"
              class="rounded-md bg-white text-gray-400 hover:text-gray-500"
              @click="$emit('close')"
            >
              <span class="sr-only">Close</span>
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="sm:flex sm:items-start">
            <div class="w-full">
              <div class="mt-3 text-center sm:mt-0 sm:text-left">
                <h3 class="text-lg font-semibold leading-6 text-gray-900">Sign Document</h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">Draw your signature below or type it</p>
                </div>
              </div>

              <div class="mt-4">
                <div class="flex space-x-4 mb-4">
                  <button
                    :class="[
                      'px-4 py-2 text-sm font-medium rounded-md',
                      signatureType === 'draw' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300'
                    ]"
                    @click="signatureType = 'draw'"
                  >
                    Draw
                  </button>
                  <button
                    :class="[
                      'px-4 py-2 text-sm font-medium rounded-md',
                      signatureType === 'type' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300'
                    ]"
                    @click="signatureType = 'type'"
                  >
                    Type
                  </button>
                </div>

                <div v-if="signatureType === 'draw'" class="border rounded-lg p-4">
                  <canvas
                    ref="canvas"
                    class="border border-gray-300 rounded-lg w-full"
                    @mousedown="startDrawing"
                    @mousemove="draw"
                    @mouseup="stopDrawing"
                    @mouseleave="stopDrawing"
                    @touchstart="startDrawing"
                    @touchmove="draw"
                    @touchend="stopDrawing"
                  ></canvas>
                  <div class="mt-2 flex justify-end">
                    <button
                      class="px-3 py-1 text-sm text-gray-600 hover:text-gray-900"
                      @click="clearCanvas"
                    >
                      Clear
                    </button>
                  </div>
                </div>

                <div v-else class="border rounded-lg p-4">
                  <input
                    type="text"
                    v-model="typedSignature"
                    class="w-full p-2 border border-gray-300 rounded-md font-signature text-xl"
                    placeholder="Type your signature"
                  />
                </div>
              </div>

              <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700">Preview</label>
                <div class="mt-1 p-4 border border-gray-300 rounded-lg min-h-[100px] flex items-center justify-center">
                  <img v-if="signatureType === 'draw' && signatureImage" :src="signatureImage" class="max-h-[80px]" />
                  <p v-else-if="signatureType === 'type' && typedSignature" class="font-signature text-xl">
                    {{ typedSignature }}
                  </p>
                  <p v-else class="text-gray-400">Your signature will appear here</p>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
            <button
              type="button"
              class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto"
              @click="sign"
              :disabled="!canSign"
            >
              Sign Document
            </button>
            <button
              type="button"
              class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
              @click="$emit('close')"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SignDocument',
  props: {
    show: {
      type: Boolean,
      required: true
    },
    document: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      signatureType: 'draw',
      isDrawing: false,
      context: null,
      typedSignature: '',
      signatureImage: null
    }
  },
  computed: {
    canSign() {
      return (this.signatureType === 'draw' && this.signatureImage) ||
             (this.signatureType === 'type' && this.typedSignature.trim().length > 0)
    }
  },
  mounted() {
    if (this.$refs.canvas) {
      this.initCanvas()
    }
  },
  methods: {
    initCanvas() {
      const canvas = this.$refs.canvas
      canvas.width = canvas.offsetWidth
      canvas.height = 200
      this.context = canvas.getContext('2d')
      this.context.strokeStyle = '#000'
      this.context.lineWidth = 2
      this.context.lineCap = 'round'
    },
    startDrawing(e) {
      this.isDrawing = true
      const pos = this.getPosition(e)
      this.context.beginPath()
      this.context.moveTo(pos.x, pos.y)
    },
    draw(e) {
      if (!this.isDrawing) return
      e.preventDefault()
      const pos = this.getPosition(e)
      this.context.lineTo(pos.x, pos.y)
      this.context.stroke()
    },
    stopDrawing() {
      this.isDrawing = false
      this.updateSignatureImage()
    },
    getPosition(e) {
      const canvas = this.$refs.canvas
      const rect = canvas.getBoundingClientRect()
      const clientX = e.touches ? e.touches[0].clientX : e.clientX
      const clientY = e.touches ? e.touches[0].clientY : e.clientY
      return {
        x: clientX - rect.left,
        y: clientY - rect.top
      }
    },
    clearCanvas() {
      this.context.clearRect(0, 0, this.$refs.canvas.width, this.$refs.canvas.height)
      this.signatureImage = null
    },
    updateSignatureImage() {
      this.signatureImage = this.$refs.canvas.toDataURL()
    },
    async sign() {
      const signature = this.signatureType === 'draw' ? this.signatureImage : this.typedSignature

      try {
        const response = await fetch(`/api/documents/${this.document.id}/sign`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({
            signature: signature,
            position: {
              x: 100,
              y: 100,
              page: 1
            }
          })
        })

        if (!response.ok) {
          throw new Error('Failed to sign document')
        }

        const result = await response.json()
        this.$emit('signed', result)
        this.$emit('close')
      } catch (error) {
        console.error('Signing error:', error)
        alert('Failed to sign document. Please try again.')
      }
    }
  },
  watch: {
    show(newVal) {
      if (newVal && this.$refs.canvas) {
        this.$nextTick(() => {
          this.initCanvas()
        })
      }
    }
  }
}
</script>

<style>
    .font-signature {
    font-family: 'Dancing Script', cursive;
    }

</style>
