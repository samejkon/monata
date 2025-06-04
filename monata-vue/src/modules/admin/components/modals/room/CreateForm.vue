<script setup>
import { ref, onMounted } from 'vue'
import { Modal } from 'bootstrap'
import { api } from '@/modules/admin/lib/axios'
import { useToast } from 'vue-toastification'

const toast = useToast();

const emit = defineEmits(['room-created', 'room-updated'])

const props = defineProps({
  modalId: {
    type: String,
    required: true
  },
  modalTitle: {
    type: String,
    default: 'New Room'
  },
  initialRoomData: {
    type: Object,
    default: () => ({
      name: '',
      room_type: '',
      description: '',
      status: 1,
      thumbnail: null,
      images: []
    })
  },
  roomTypesApiUrl: {
    type: String,
    required: true
  }
})

const room = ref({ ...props.initialRoomData })
const thumbnailPreview = ref(props.initialRoomData.thumbnail)
const roomTypes = ref([])
const loadingRoomTypes = ref(false)
const errorRoomTypes = ref(null)
const validationErrors = ref({})

onMounted(async () => {
  await fetchRoomTypes()
})

const fetchRoomTypes = async () => {
  loadingRoomTypes.value = true
  errorRoomTypes.value = null

  try {
    const response = await api.get('/room-types')
    roomTypes.value = response.data.data
  } catch (error) {
    console.error('Error fetching room types:', error)
    errorRoomTypes.value = 'Can not fetch room types.'
  } finally {
    loadingRoomTypes.value = false
  }
}

const handleThumbnailChange = (event) => {
  const file = event.target.files[0]
  room.value.thumbnail = file
  thumbnailPreview.value = file ? URL.createObjectURL(file) : null
}

const addImages = (event) => {
  const files = event.target.files
  if (files && files.length > 0) {
    for (let i = 0; i < files.length; i++) {
      const file = files[i]
      if (file.type.startsWith('image/')) {
        room.value.images.push({
          file: file,
          preview: URL.createObjectURL(file)
        })
      } else {
        console.warn('File is not an image:', file);
      }
    }
  }
}

const removeImage = (index) => {
  room.value.images.splice(index, 1)
}

const resetForm = () => {
  room.value = { ...props.initialRoomData }
  thumbnailPreview.value = props.initialRoomData.thumbnail
  validationErrors.value = {}
}

const submitForm = async () => {
  validationErrors.value = {}
  const formData = new FormData()
  formData.append('name', room.value.name)
  formData.append('room_type_id', room.value.room_type)
  formData.append('description', room.value.description)
  formData.append('status', room.value.status)

  if (room.value.thumbnail) {
    formData.append('thumbnail', room.value.thumbnail)
  }

  room.value.images.forEach(imageObj => {
    formData.append('images[]', imageObj.file)
  })

  try {
    const response = await api.post('/rooms', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    emit('room-created', response.data)
    resetForm()
    const modal = Modal.getInstance(document.getElementById(props.modalId))

    if (modal) {
      modal.hide()
    }

    toast.success('Room created successfully!')
  } catch (error) {
    if (error.response && error.response.status === 422) {
      validationErrors.value = error.response.data.errors
    } else {
      toast.error('Fail to create room!')
      console.error('Error sending data:', error)
    }
  }
}
</script>

<template>
  <div class="modal fade" :id="modalId" tabindex="-1" aria-labelledby="roomFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="roomFormModalLabel">{{ modalTitle }}</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="submitForm">
            <div class="mb-3">
              <label for="name" class="form-label">Name:</label>
              <input type="text" class="form-control" id="name" v-model="room.name">
              <div v-if="validationErrors.name" class="invalid-feedback d-block">
                {{ validationErrors.name[0] }}
              </div>
            </div>

            <div class="mb-3 row">
              <div class="col-md-6">
                <label for="room_type" class="form-label">Type:</label>
                <select class="form-select" id="room_type" v-model="room.room_type">
                  <option v-for="type in roomTypes" :key="type.id" :value="type.id">{{ type.name }}
                  </option>
                </select>
                <div v-if="validationErrors.room_type_id" class="invalid-feedback d-block">
                  {{ validationErrors.room_type_id[0] }}
                </div>
              </div>
              <div class="col-md-6">
                <label for="status" class="form-label">Status:</label>
                <select class="form-select" id="status" v-model="room.status">
                  <option value="1">Active</option>
                  <option value="2">Booked</option>
                  <option value="3">Occupied</option>
                  <option value="4">Cleaning</option>
                  <option value="5">Inactive</option>
                </select>
              </div>
            </div>

            <div class="mb-3">
              <label for="description" class="form-label">Description:</label>
              <textarea class="form-control" id="description" v-model="room.description" rows="3"></textarea>
              <div v-if="validationErrors.description" class="invalid-feedback d-block">
                {{ validationErrors.description[0] }}
              </div>
            </div>

            <div class="mb-3">
              <label for="thumbnail" class="form-label">Thumbnail:</label>
              <input type="file" class="form-control" id="thumbnail" @change="handleThumbnailChange" accept="image/*">
              <div v-if="thumbnailPreview" class="mt-2">
                <img :src="thumbnailPreview" alt="Thumbnail Preview" class="thumbnail-preview-img">
              </div>
              <div v-if="validationErrors.thumbnail" class="invalid-feedback d-block">
                {{ validationErrors.thumbnail[0] }}
              </div>
            </div>

            <div class="mb-3">
              <label for="images" class="form-label">Add Room Images (Select Multiple):</label>
              <input type="file" class="form-control" id="images" @change="addImages" accept="image/*" multiple>
              <div v-if="validationErrors.images" class="invalid-feedback d-block">
                {{ validationErrors.images[0] }}
              </div>
            </div>

            <div v-if="room.images.length > 0" class="mb-3">
              <label class="form-label">Selected Images:</label>
              <div class="d-flex flex-wrap">
                <div v-for="(image, index) in room.images" :key="index" class="position-relative m-2 image-container">
                  <img :src="image.preview" alt="Image Preview" class="img-thumbnail image-display">
                  <button type="button" class="btn-close btn-close-white btn-sm position-absolute top-0 end-0"
                    aria-label="Remove" @click="removeImage(index)"></button>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary">Save</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.btn-close {
  background-color: rgba(255, 255, 255, 0.7);
  border-radius: 50%;
  opacity: 0.8;
}

.btn-close:hover {
  opacity: 1;
}

.thumbnail-preview-img {
  max-width: 100px;
  max-height: 100px;
}

.image-container {
  width: 100px;
  height: 100px;
}

.image-display {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
</style>
