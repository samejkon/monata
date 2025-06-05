<template>
    <section class="contact-section">
        <div class="container">
            <div class="d-none d-sm-block mb-5 pb-4">
                <div id="map" style="height: 480px; position: relative; overflow: hidden;">
                    <iframe v-show="!mapError"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3723.791311446155!2d105.7692875!3d21.0374662!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x313454b7e7c379cd%3A0x6130fa573351b263!2z4bqgIFZp4buHdCBCdWlsZGluZw!5e0!3m2!1svi!2s!4v1716981300000!5m2!1svi!2s"
                        style="width: 100%; height: 100%; border: 0" :allowfullscreen="true" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade" @error="handleMapError" />
                    <div v-show="mapError" class="gm-err-container"
                        style="height: 100%; width: 100%; position: absolute; top: 0; left: 0; background-color: rgb(229, 227, 223)">
                        <div class="gm-err-content">
                            <div class="gm-err-icon">
                                <img src="https://maps.gstatic.com/mapfiles/api-3/images/icon_error.png"
                                    alt="Error icon" draggable="false" style="user-select: none" />
                            </div>
                            <div class="gm-err-title">Oops! Something went wrong.</div>
                            <div class="gm-err-message">This page didn't load Google Maps correctly. See the JavaScript
                                console for technical details.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <h2 class="contact-title">Get in Touch</h2>
                </div>
                <div class="col-lg-8">
                    <form class="form-contact contact_form" @submit.prevent="submitForm" id="contactForm">
                        <div class="row">
                            <template v-if="!authStore.authenticated">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <input v-model="formData.guest_name" class="form-control"
                                            :class="{ 'is-invalid': errors.guest_name }" name="guest_name" type="text"
                                            placeholder="Enter your name">
                                        <div class="invalid-feedback" v-if="errors.guest_name">{{ errors.guest_name }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <input v-model="formData.guest_email" class="form-control"
                                            :class="{ 'is-invalid': errors.guest_email }" name="guest_email"
                                            type="email" placeholder="Email">
                                        <div class="invalid-feedback" v-if="errors.guest_email">{{ errors.guest_email }}
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div class="col-12">
                                <div class="form-group">
                                    <input v-model="formData.title" class="form-control"
                                        :class="{ 'is-invalid': errors.title }" name="title" type="text"
                                        placeholder="Enter title">
                                    <div class="invalid-feedback" v-if="errors.title">{{ errors.title }}</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <textarea v-model="formData.content" class="form-control w-100"
                                        :class="{ 'is-invalid': errors.content }" name="content" cols="30" rows="9"
                                        placeholder="Enter Message"></textarea>
                                    <div class="invalid-feedback" v-if="errors.content">{{ errors.content }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-outline-primary" :disabled="isSubmitting">
                                {{ isSubmitting ? 'Sending...' : 'Send' }}
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-lg-3 offset-lg-1">
                    <div class="media contact-info">
                        <span class="contact-info__icon">
                            <HouseIcon />
                        </span>
                        <div class="media-body">
                            <h3>Buttonwood, California.</h3>
                            <p>Rosemead, CA 91770</p>
                        </div>
                    </div>
                    <div class="media contact-info">
                        <span class="contact-info__icon">
                            <PhoneIcon />
                        </span>
                        <div class="media-body">
                            <h3>+1 253 565 2365</h3>
                            <p>Mon to Fri 9am to 6pm</p>
                        </div>
                    </div>
                    <div class="media contact-info">
                        <span class="contact-info__icon">
                            <MailIcon />
                        </span>
                        <div class="media-body">
                            <h3>support@colorlib.com</h3>
                            <p>Send us your query anytime!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import type { Contact } from "../../../admin/stores/model/Contact.model"
import { HouseIcon, PhoneIcon, MailIcon } from 'lucide-vue-next'
import { csrf, api } from '../../lib/axios'
import { useAuthStore } from '@/modules/customer/stores/auth'

const authStore = useAuthStore()
const mapError = ref(false)

const handleMapError = () => {
    mapError.value = true
}

const sendContact = async (data: Partial<Contact>) => {
    await csrf.get('/sanctum/csrf-cookie')
    const response = await api.post('/contacts/send-contact', data)
    return response.data.data
}

interface ContactForm {
    guest_name?: string
    guest_email?: string
    title: string
    content: string
    user_id?: number
}

interface FormErrors {
    guest_name?: string
    guest_email?: string
    title?: string
    content?: string
}

interface ApiError {
    message: string
    errors?: {
        [key: string]: string[]
    }
}

const formData = reactive<ContactForm>({
    guest_name: '',
    guest_email: '',
    title: '',
    content: ''
})

const errors = reactive<FormErrors>({})
const isSubmitting = ref(false)

const handleApiErrors = (error: any) => {
    // Reset errors
    Object.keys(errors).forEach(key => {
        errors[key as keyof FormErrors] = ''
    })

    if (error.response?.data) {
        const apiError = error.response.data as ApiError

        if (apiError.errors) {
            Object.entries(apiError.errors).forEach(([field, messages]) => {
                const fieldName = field.replace(/_([a-z])/g, (_, letter) => letter.toUpperCase())
                if (fieldName in errors) {
                    errors[fieldName as keyof FormErrors] = messages[0]
                }
            })
        } else if (apiError.message) {
            alert(apiError.message)
        }
    } else {
        // Lỗi không xác định
        alert('Có lỗi xảy ra khi gửi tin nhắn. Vui lòng thử lại sau!')
    }
}

const submitForm = async () => {
    try {
        isSubmitting.value = true

        const dataToSend: ContactForm = {
            title: formData.title,
            content: formData.content
        }

        if (authStore.authenticated) {
            try {
                const response = await api.get('/profile')
                dataToSend.user_id = response.data.data.id
            } catch (error: any) {
                console.error('Lỗi khi lấy thông tin user:', error.response?.data)
                throw new Error('Không thể lấy thông tin người dùng')
            }
        } else {
            dataToSend.guest_name = formData.guest_name
            dataToSend.guest_email = formData.guest_email
        }

        const response = await sendContact(dataToSend)

        alert('Gửi tin nhắn thành công!')

        formData.guest_name = ''
        formData.guest_email = ''
        formData.title = ''
        formData.content = ''
        Object.keys(errors).forEach(key => {
            errors[key as keyof FormErrors] = ''
        })

    } catch (error: any) {
        console.error('Chi tiết lỗi:', error.response?.data) // Debug log
        handleApiErrors(error)
    } finally {
        isSubmitting.value = false
    }
}
</script>

<style scoped>
.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.gm-err-container {
    display: flex;
    align-items: center;
    justify-content: center;
}

.gm-err-content {
    text-align: center;
    padding: 20px;
}

.gm-err-icon {
    margin-bottom: 10px;
}

.gm-err-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px;
}

.gm-err-message {
    font-size: 14px;
    color: #666;
}
</style>
