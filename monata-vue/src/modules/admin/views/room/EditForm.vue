<script setup>
import { ref, onMounted } from 'vue'
import { Modal } from 'bootstrap'
import { api, csrf } from '@/modules/admin/lib/axios'

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
            id: null, // Thêm ID để phân biệt tạo mới/chỉnh sửa
            name: '',
            room_type: '',
            description: '',
            status: 1,
            thumbnail: null,
            images: [],
            existing_images: [] // Để chứa thông tin ảnh hiện tại khi chỉnh sửa
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
const imagesToRemove = ref([])

onMounted(async () => {
    await fetchRoomTypes()
    // Khi component được mount và ở chế độ chỉnh sửa, gán ảnh hiện tại
    if (props.initialRoomData.existing_images) {
        // Đảm bảo là một bản sao để tránh ảnh hưởng trực tiếp đến prop
        room.value.existing_images = [...props.initialRoomData.existing_images];
    }
})

const fetchRoomTypes = async () => {
    loadingRoomTypes.value = true
    errorRoomTypes.value = null
    try {
        const response = await api.get('admin/room-types')
        roomTypes.value = response.data.data
    } catch (error) {
        console.error('Lỗi khi tải loại phòng:', error)
        errorRoomTypes.value = 'Không thể tải danh sách loại phòng.'
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
                console.warn('Tệp không phải là hình ảnh:', file);
            }
        }
    }
}

const removeNewImage = (index) => {
    room.value.images.splice(index, 1)
}

const markImageForRemoval = (imageId) => {
    const index = imagesToRemove.value.indexOf(imageId)
    if (index === -1) {
        imagesToRemove.value.push(imageId)
    } else {
        imagesToRemove.value.splice(index, 1)
    }
    console.log('Ảnh cần xóa:', imagesToRemove.value)
}

const resetForm = () => {
    room.value = { ...props.initialRoomData }
    thumbnailPreview.value = props.initialRoomData.thumbnail
    imagesToRemove.value = []
}

const submitForm = async () => {
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
    imagesToRemove.value.forEach(imageId => {
        formData.append('images_to_remove[]', imageId)
    })

    try {
        let response;
        if (props.initialRoomData.id) {
            response = await api.post(`admin/rooms/${props.initialRoomData.id}`, formData, { // Đảm bảo API route này là để UPDATE
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            emit('room-updated', response.data)
        } else {
            response = await api.post('admin/rooms', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            emit('room-created', response.data)
        }
        console.log('Dữ liệu đã được gửi thành công:', response.data)
        resetForm()
        const modal = Modal.getInstance(document.getElementById(props.modalId))
        if (modal) {
            modal.hide()
        }
    } catch (error) {
        console.error('Lỗi khi gửi dữ liệu:', error)
    }
};
</script>

<template>
    <div class="modal fade" :id="modalId" tabindex="-1" aria-labelledby="roomFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="roomFormModalLabel">{{ modalTitle }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="submitForm">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name:</label>
                            <input type="text" class="form-control" id="name" v-model="room.name" required>
                        </div>

                        <div class="mb-3 row">
                            <div class="col-md-6">
                                <label for="room_type" class="form-label">Type:</label>
                                <select class="form-select" id="room_type" v-model="room.room_type" required>
                                    <option v-for="type in roomTypes" :key="type.id" :value="type.id">{{ type.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status:</label>
                                <select class="form-select" id="status" v-model="room.status">
                                    <option value="2">Active</option>
                                    <option value="1">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description:</label>
                            <textarea class="form-control" id="description" v-model="room.description"
                                rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="thumbnail" class="form-label">Thumbnail:</label>
                            <input type="file" class="form-control" id="thumbnail" @change="handleThumbnailChange"
                                accept="image/*">
                            <div v-if="thumbnailPreview" class="mt-2">
                                <img :src="thumbnailPreview" alt="Thumbnail Preview"
                                    style="max-width: 100px; max-height: 100px;">
                            </div>
                        </div>

                        <div v-if="props.initialRoomData.existing_images && props.initialRoomData.existing_images.length > 0"
                            class="mb-3">
                            <label class="form-label">Current Images:</label>
                            <div class="d-flex flex-wrap">
                                <div v-for="image in props.initialRoomData.existing_images" :key="image.id"
                                    class="position-relative m-2" style="width: 100px; height: 100px;"
                                    :class="{ 'image-to-remove': imagesToRemove.includes(image.id) }">
                                    <img :src="image.image_path" alt="Current Image" class="img-thumbnail"
                                        style="width: 100%; height: 100%; object-fit: cover;">
                                    <button type="button"
                                        class="btn-close btn-close-white btn-sm position-absolute top-0 end-0"
                                        aria-label="Remove" @click="markImageForRemoval(image.id)"></button>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="images" class="form-label">Add Room Images (Select Multiple):</label>
                            <input type="file" class="form-control" id="images" @change="addImages" accept="image/*"
                                multiple>
                        </div>

                        <div v-if="room.images.length > 0" class="mb-3">
                            <label class="form-label">New Images to Upload:</label>
                            <div class="d-flex flex-wrap">
                                <div v-for="(image, index) in room.images" :key="'new_' + index"
                                    class="position-relative m-2" style="width: 100px; height: 100px;">
                                    <img :src="image.preview" alt="Image Preview" class="img-thumbnail"
                                        style="width: 100%; height: 100%; object-fit: cover;">
                                    <button type="button"
                                        class="btn-close btn-close-white btn-sm position-absolute top-0 end-0"
                                        aria-label="Remove" @click="removeNewImage(index)"></button>
                                </div>
                            </div>
                        </div>

                        <div v-if="imagesToRemove.length > 0" class="mb-3">
                            <label class="form-label">Images Marked for Removal:</label>
                            <div class="d-flex flex-wrap">
                                <div v-for="imageId in imagesToRemove" :key="'remove_' + imageId"
                                    class="position-relative m-2" style="width: 100px; height: 100px;">
                                    <img v-if="props.initialRoomData.existing_images"
                                        :src="props.initialRoomData.existing_images.find(img => img.id === imageId)?.image_path"
                                        alt="Image to Remove" class="img-thumbnail"
                                        style="width: 100%; height: 100%; object-fit: cover;">
                                    <button type="button"
                                        class="btn btn-sm btn-outline-secondary position-absolute bottom-0 end-0 m-1"
                                        @click="markImageForRemoval(imageId)">Undo Remove</button>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">{{ props.initialRoomData.id ? 'Save Changes' :
                                'Save' }}</button>
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

.image-to-remove {
    opacity: 0.6;
    border: 2px solid red;
}
</style>
