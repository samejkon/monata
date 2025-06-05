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
              <editor api-key="mw4qmrce6512eg9eejrdl3izr2a7k6j1doyvp0f6btppb20q" :init="tinyMceConfig"
                v-model="room.description" />
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
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="resetForm">Close</button>
              <button type="submit" class="btn btn-primary">Save</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Modal } from 'bootstrap' // Đảm bảo Bootstrap JS đã được import vào dự án Vue của bạn
import { api } from '@/modules/admin/lib/axios' // Đường dẫn đến instance Axios của bạn
import { useToast } from 'vue-toastification' // Thư viện Toastification
import Editor from '@tinymce/tinymce-vue'; // Component TinyMCE Editor

// Khởi tạo Toast notification
const toast = useToast();

// Định nghĩa các sự kiện mà component này có thể emit ra ngoài
const emit = defineEmits(['room-created', 'room-updated'])

// Định nghĩa các props mà component này nhận vào
const props = defineProps({
  modalId: { // ID của modal, dùng để điều khiển modal bằng Bootstrap JS
    type: String,
    required: true
  },
  modalTitle: { // Tiêu đề của modal
    type: String,
    default: 'New Room'
  },
  initialRoomData: { // Dữ liệu khởi tạo cho form (khi chỉnh sửa phòng hiện có)
    type: Object,
    default: () => ({
      name: '',
      room_type: '',
      description: '', // Mặc định rỗng cho TinyMCE
      status: 1,
      thumbnail: null,
      images: []
    })
  },
  roomTypesApiUrl: { // Prop này có thể không cần thiết nếu bạn hardcode '/room-types'
    type: String,
    required: false // Đổi thành false vì bạn đang dùng api.get('/room-types') trực tiếp
  }
})

// Các Ref để quản lý trạng thái của form và dữ liệu
const room = ref({ ...props.initialRoomData }) // Dữ liệu của phòng, sẽ được gửi đi
const thumbnailPreview = ref(props.initialRoomData.thumbnail) // URL tạm thời cho thumbnail
const roomTypes = ref([]) // Danh sách các loại phòng
const loadingRoomTypes = ref(false) // Trạng thái tải loại phòng
const errorRoomTypes = ref(null) // Lỗi khi tải loại phòng
const validationErrors = ref({}) // Lưu trữ lỗi validation từ Laravel

// --- Cấu hình TinyMCE Editor ---
const tinyMceConfig = {
  height: 300, // Chiều cao của editor
  menubar: false, // Ẩn thanh menu trên cùng (File, Edit, View, ...)
  // Các plugin cung cấp các tính năng cho editor
  plugins: [
    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
    'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
  ],
  // Các nút trên thanh công cụ của editor
  toolbar:
    'undo redo | formatselect | ' +
    'bold italic backcolor | alignleft aligncenter ' +
    'alignright alignjustify | bullist numlist outdent indent | ' +
    'removeformat | image link code fullscreen help',
  // CSS áp dụng cho nội dung bên trong editor
  content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',

  // Hàm xử lý upload ảnh từ editor lên server của bạn
  images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
    const formData = new FormData();
    formData.append('file', blobInfo.blob(), blobInfo.filename());

    // --- RẤT QUAN TRỌNG: THAY THẾ BẰNG URL API UPLOAD ẢNH CỦA LARAVEL BACKEND CỦA BẠN ---
    // Ví dụ: 'http://localhost:8000/api/upload-image'
    // Đảm bảo URL này khớp với route POST '/upload-image' trong Laravel của bạn
    const uploadUrl = 'http://localhost:8000/api/upload-image'; // <-- ĐẶT URL LARAVEL CỦA BẠN TẠI ĐÂY

    // Gửi request POST đến API Laravel của bạn để upload ảnh
    api.post(uploadUrl, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        // Thêm các header cần thiết nếu API yêu cầu (ví dụ: Authorization Token cho xác thực API)
        // 'Authorization': `Bearer ${localStorage.getItem('your_auth_token')}`
      },
      // Cập nhật tiến độ tải lên (nếu muốn hiển thị thanh tiến độ trong TinyMCE)
      onUploadProgress: (e) => {
        progress(e.loaded / e.total * 100);
      }
    })
      .then(response => {
        // Laravel cần trả về JSON có dạng { location: 'url_cua_anh_da_upload' }
        if (response.data && response.data.location) {
          resolve(response.data.location); // Trả về URL ảnh đã upload cho TinyMCE
        } else {
          reject('Invalid upload response from server. Expected { location: "url" }');
        }
      })
      .catch(error => {
        // Xử lý lỗi khi upload ảnh
        console.error('Image upload failed:', error.response ? error.response.data : error.message);
        reject('Image upload failed: ' + (error.response ? error.response.data.message : error.message));
      });
  }),
};

// --- Lifecycle Hook: Khi component được mount ---
onMounted(async () => {
  await fetchRoomTypes() // Tải danh sách loại phòng khi component được khởi tạo
})

// --- Các hàm xử lý sự kiện và logic ---

// Hàm tải danh sách loại phòng từ API
const fetchRoomTypes = async () => {
  loadingRoomTypes.value = true
  errorRoomTypes.value = null

  try {
    const response = await api.get('/room-types') // API endpoint để lấy danh sách loại phòng
    roomTypes.value = response.data.data // Giả sử API trả về { data: [...] }
  } catch (error) {
    console.error('Error fetching room types:', error)
    errorRoomTypes.value = 'Can not fetch room types.'
  } finally {
    loadingRoomTypes.value = false
  }
}

// Xử lý khi người dùng chọn file thumbnail
const handleThumbnailChange = (event) => {
  const file = event.target.files[0]
  room.value.thumbnail = file // Gán file vào dữ liệu phòng
  thumbnailPreview.value = file ? URL.createObjectURL(file) : null // Tạo URL tạm thời để hiển thị preview
}

// Xử lý khi người dùng chọn nhiều ảnh cho phòng
const addImages = (event) => {
  const files = event.target.files
  if (files && files.length > 0) {
    for (let i = 0; i < files.length; i++) {
      const file = files[i]
      if (file.type.startsWith('image/')) { // Chỉ thêm các file là ảnh
        room.value.images.push({
          file: file,
          preview: URL.createObjectURL(file)
        })
      } else {
        console.warn('File is not an image:', file);
        toast.warning(`File "${file.name}" is not an image and was skipped.`);
      }
    }
  }
}

// Xóa một ảnh đã chọn khỏi danh sách
const removeImage = (index) => {
  room.value.images.splice(index, 1) // Xóa ảnh tại vị trí index
}

// Reset form về trạng thái ban đầu
const resetForm = () => {
  room.value = { ...props.initialRoomData } // Reset dữ liệu phòng
  thumbnailPreview.value = props.initialRoomData.thumbnail // Reset thumbnail preview
  validationErrors.value = {} // Xóa lỗi validation
  // Đảm bảo input file cũng được reset (bằng cách xóa giá trị của nó)
  const thumbnailInput = document.getElementById('thumbnail');
  if (thumbnailInput) thumbnailInput.value = '';
  const imagesInput = document.getElementById('images');
  if (imagesInput) imagesInput.value = '';
}

// Hàm gửi form đến API Laravel
const submitForm = async () => {
  validationErrors.value = {} // Reset lỗi validation trước khi gửi
  const formData = new FormData() // Sử dụng FormData để gửi file và dữ liệu

  // Thêm các trường dữ liệu văn bản vào FormData
  formData.append('name', room.value.name)
  formData.append('room_type_id', room.value.room_type)
  formData.append('description', room.value.description) // Nội dung HTML từ TinyMCE
  formData.append('status', room.value.status)

  // Thêm thumbnail nếu có
  if (room.value.thumbnail) {
    formData.append('thumbnail', room.value.thumbnail)
  }

  // Thêm các ảnh phụ nếu có. Sử dụng 'images[]' hoặc 'images[index]' để Laravel nhận diện là một mảng file.
  room.value.images.forEach((imageObj, index) => {
    formData.append(`images[${index}]`, imageObj.file)
  })

  try {
    // Gửi request POST đến API Laravel để tạo phòng mới
    const response = await api.post('/rooms', formData, {
      headers: {
        // Rất quan trọng: 'Content-Type': 'multipart/form-data' khi gửi file
        'Content-Type': 'multipart/form-data'
      }
    })

    emit('room-created', response.data) // Emit sự kiện khi tạo phòng thành công
    resetForm() // Reset form sau khi gửi thành công

    // Ẩn modal Bootstrap
    const modalElement = document.getElementById(props.modalId)
    const modal = Modal.getInstance(modalElement)

    if (modal) {
      modal.hide()
    }

    toast.success('Room created successfully!') // Hiển thị thông báo thành công
  } catch (error) {
    // Xử lý lỗi từ API
    if (error.response && error.response.status === 422) {
      // Lỗi validation từ Laravel (status 422)
      validationErrors.value = error.response.data.errors
      toast.error('Validation Error: Please check your input.')
    } else {
      // Các lỗi khác
      toast.error('Failed to create room!')
      console.error('Error sending data:', error)
    }
  }
}
</script>

<style scoped>
/* CSS cho nút đóng modal */
.btn-close {
  background-color: rgba(255, 255, 255, 0.7);
  border-radius: 50%;
  opacity: 0.8;
}

.btn-close:hover {
  opacity: 1;
}

/* CSS cho ảnh thumbnail preview */
.thumbnail-preview-img {
  max-width: 100px;
  max-height: 100px;
  object-fit: cover;
  /* Đảm bảo hình ảnh không bị méo */
  border: 1px solid #ddd;
  padding: 2px;
  border-radius: 4px;
}

/* CSS cho container ảnh phụ */
.image-container {
  width: 100px;
  height: 100px;
  margin: 5px;
  /* Khoảng cách giữa các ảnh */
  position: relative;
  border: 1px solid #eee;
  border-radius: 4px;
  overflow: hidden;
  /* Đảm bảo ảnh không tràn ra ngoài container */
}

/* CSS cho ảnh phụ hiển thị */
.image-display {
  width: 100%;
  height: 100%;
  object-fit: cover;
  /* Đảm bảo hình ảnh không bị méo */
}

/* CSS cho nút xóa ảnh phụ */
.image-container .btn-close {
  position: absolute;
  top: 5px;
  right: 5px;
  z-index: 10;
  width: 20px;
  height: 20px;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 0.75rem;
  /* Kích thước icon nhỏ hơn */
}

/* Style cơ bản để TinyMCE trông giống Bootstrap form-control */
.tox .tox-tinymce {
  border: 1px solid #ced4da;
  border-radius: 0.25rem;
}
</style>
