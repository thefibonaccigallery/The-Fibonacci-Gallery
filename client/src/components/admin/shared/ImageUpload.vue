<script setup lang="ts">
import { ref, computed } from 'vue'
import { PhUpload, PhX, PhImage } from '@phosphor-icons/vue'
import { Button } from '@/components/ui/button'

interface Props {
  modelValue?: File | null
  currentImageUrl?: string | null
  maxSize?: number // in MB
  accept?: string
}

const props = withDefaults(defineProps<Props>(), {
  maxSize: 2,
  accept: 'image/jpeg,image/png,image/jpg,image/gif,image/webp',
})

const emit = defineEmits<{
  'update:modelValue': [file: File | null]
  'delete': []
}>()

const fileInput = ref<HTMLInputElement | null>(null)
const previewUrl = ref<string | null>(null)
const error = ref<string | null>(null)

const displayUrl = computed(() => {
  if (previewUrl.value) return previewUrl.value
  if (props.currentImageUrl) return props.currentImageUrl
  return null
})

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]

  if (!file) return

  error.value = null

  // Validate file type
  if (!file.type.startsWith('image/')) {
    error.value = 'Please select an image file'
    return
  }

  // Validate file size
  const maxSizeBytes = props.maxSize * 1024 * 1024
  if (file.size > maxSizeBytes) {
    error.value = `File size must be less than ${props.maxSize}MB`
    return
  }

  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    previewUrl.value = e.target?.result as string
  }
  reader.readAsDataURL(file)

  emit('update:modelValue', file)
}

const handleRemove = () => {
  previewUrl.value = null
  error.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
  emit('update:modelValue', null)
}

const handleDelete = () => {
  handleRemove()
  emit('delete')
}

const triggerFileInput = () => {
  fileInput.value?.click()
}
</script>

<template>
  <div class="space-y-4">
    <!-- Preview -->
    <div
      v-if="displayUrl"
      class="relative aspect-video rounded-lg overflow-hidden bg-muted border border-border"
    >
      <img :src="displayUrl" alt="Preview" class="w-full h-full object-cover" />
      <div class="absolute top-2 right-2 flex gap-2">
        <Button
          @click="handleRemove"
          size="sm"
          variant="destructive"
          class="h-8 w-8 p-0"
          type="button"
        >
          <PhX :size="16" />
        </Button>
      </div>
    </div>

    <!-- Upload Area -->
    <div
      v-else
      @click="triggerFileInput"
      class="aspect-video rounded-lg border-2 border-dashed border-border hover:border-primary transition-colors cursor-pointer bg-muted/50 flex flex-col items-center justify-center gap-3"
    >
      <PhImage :size="48" class="text-muted-foreground" />
      <div class="text-center">
        <p class="text-sm font-medium text-foreground">Click to upload image</p>
        <p class="text-xs text-muted-foreground mt-1">
          PNG, JPG, GIF or WEBP (max {{ maxSize }}MB)
        </p>
      </div>
    </div>

    <!-- Hidden File Input -->
    <input
      ref="fileInput"
      type="file"
      :accept="accept"
      @change="handleFileSelect"
      class="hidden"
    />

    <!-- Error Message -->
    <p v-if="error" class="text-sm text-destructive">{{ error }}</p>

    <!-- Delete Current Image Button -->
    <Button
      v-if="currentImageUrl && !previewUrl"
      @click="handleDelete"
      variant="outline"
      size="sm"
      type="button"
      class="w-full text-destructive hover:text-destructive"
    >
      <PhX :size="16" class="mr-2" />
      Delete Current Image
    </Button>
  </div>
</template>
