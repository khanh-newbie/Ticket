<template>
  <div class="p-4 bg-[#23252C] rounded-lg w-full mb-2">
    <label for="eventDescription" class="block text-white font-medium mb-2">
      Thông tin sự kiện <span class="text-red-500">*</span>
    </label>

    <Editor
      id="eventDescription"
      v-model="innerValue"
      api-key="viuw7g5wkwnmt8fju25i56gdhsenxugsduqjz07efu4qtj4a"
      :init="editorConfig"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import Editor from '@tinymce/tinymce-vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['update:modelValue'])

const innerValue = computed({
  get: () => props.modelValue,
  set: val => emit('update:modelValue', val),
})

const editorConfig = {
  height: 400,
  menubar: false,
  plugins: [
    'advlist',
    'autolink',
    'lists',
    'link',
    'image',
    'charmap',
    'preview',
    'anchor',
    'searchreplace',
    'visualblocks',
    'code',
    'fullscreen',
    'insertdatetime',
    'media',
    'table',
    'paste',
    'help',
    'wordcount',
  ],
  toolbar:
    'undo redo | formatselect | bold italic underline forecolor | ' +
    'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | ' +
    'removeformat | help',
  skin: 'oxide-dark',
  content_css: 'dark',
  branding: false,
  content_style: `
    body {
      background-color: #1e1f25 !important;
      color: white !important;
      font-size: 14px;
      line-height: 1.6;
      padding: 10px;
    }
    p, span { color: white !important; }
  `,
}
</script>
