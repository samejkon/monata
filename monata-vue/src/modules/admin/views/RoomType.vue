<script setup>
import axios from 'axios';
import { ref, onMounted } from 'vue';
import { Modal } from 'bootstrap';
import { Plus, SquarePen, Trash2, X } from 'lucide-vue-next';
import { useToast } from 'vue-toastification'

const url = import.meta.env.VITE_API_URL;
const toast = useToast()
const roomTypes = ref([]);
const isEditing = ref(false);

const currentRoomType = ref({
  id: null,
  name: '',
  price: '',
  properties: [],
});
let roomTypeModal = null;

const propertiesList = ref([]);

const fetchProperties = async () => {
  try {
    const response = await axios.get(`${url}/admin/properties`, { params: { per_page: 1000 } });
    propertiesList.value = response.data?.data || [];
  } catch (error) {
    propertiesList.value = [];
  }
};

const fetchRoomTypes = async () => {
  try {
    const response = await axios.get(`${url}/admin/room-types`);
    roomTypes.value = (response.data?.data || []).map(item => ({
      ...item,
      price: parseFloat(item.price),
      properties: item.properties || [],
    }));
  } catch (error) {
    roomTypes.value = [];
  }
};

onMounted(() => {
  fetchRoomTypes();
  fetchProperties();
  roomTypeModal = new Modal(document.getElementById('roomTypeModal'));
});

const formatCurrency = (value) => {
  if (!value) return '0';
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
  }).format(value);
};

const openAddModal = () => {
  isEditing.value = false;
  currentRoomType.value = {
    id: null,
    name: '',
    price: '',
    properties: [],
  };
  roomTypeModal.show();
};

const editRoomType = (roomType) => {
  isEditing.value = true;
  const uniqueProps = [];
  const seen = new Set();
  (roomType.properties || []).forEach(prop => {
    if (!seen.has(prop.property_id)) {
      uniqueProps.push({
        property_id: prop.property_id,
        value: prop.value
      });
      seen.add(prop.property_id);
    }
  });
  currentRoomType.value = {
    id: roomType.id,
    name: roomType.name,
    price: roomType.price,
    properties: uniqueProps
  };
  roomTypeModal.show();
};

const saveRoomType = async () => {
  try {
    const payload = {
      name: currentRoomType.value.name,
      price: currentRoomType.value.price,
      properties: currentRoomType.value.properties
        .filter(p => p.property_id && p.value !== '')
        .map(p => ({
          property_id: Number(p.property_id),
          value: p.value
        }))
    };
    if (isEditing.value) {
      await axios.put(`${url}/admin/room-types/${currentRoomType.value.id}`, payload);
    } else {
      await axios.post(`${url}/admin/room-types`, payload);
    }
    await fetchRoomTypes();
    roomTypeModal.hide();
    toast.success('Room type saved successfully!');
  } catch (error) {
    toast.error('Failed to save room type!');
  }
};

const deleteRoomType = async (id) => {
  if (confirm('Are you sure you want to delete this room type?')) {
    try {
      await axios.delete(`${url}/admin/room-types/${id}`);
      await fetchRoomTypes();
      toast.success('Room type deleted successfully!');
    } catch (error) {
      toast.error('Don\'t delete this room type!');
      console.error('Error deleting room type:', error);
    }
  }
};

const addProperty = () => {
  const selectedIds = currentRoomType.value.properties.map(p => p.property_id);
  const available = propertiesList.value.find(p => !selectedIds.includes(p.id));
  if (available) {
    currentRoomType.value.properties.push({ property_id: available.id, value: '' });
  }
};

const onPropertyChange = (index) => {
  const prop = currentRoomType.value.properties[index];
  const count = currentRoomType.value.properties.filter(p => p.property_id === prop.property_id).length;
  if (count > 1) {
    prop.property_id = '';
    prop.value = '';
  }
};

const removeProperty = (index) => {
  currentRoomType.value.properties.splice(index, 1);
};

const closeModal = () => {
  if (roomTypeModal) roomTypeModal.hide();
};
</script>

<template>
  <div class="card shadow mb-4">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary">Manage Room Types</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle">
          <thead>
            <tr>
              <th scope="col" class="text-center">#</th>
              <th scope="col">Name</th>
              <th scope="col">Price</th>
              <th scope="col">Properties</th>
              <th scope="col" class="text-center">
                <button class="btn btn-success" @click="openAddModal">
                  <Plus />
                </button>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(roomType, index) in roomTypes" :key="roomType.id">
              <td class="text-center">{{ index + 1 }}</td>
              <td>{{ roomType.name }}</td>
              <td>{{ formatCurrency(roomType.price) }}</td>
              <td>
                <ul class="list-unstyled mb-0">
                  <li v-for="(prop, index) in roomType.properties" :key="index">
                    <strong>
                      {{
                        propertiesList.find(p => p.id === (prop.property_id || prop.id))?.name || prop.name || 'N/A'
                      }}:
                    </strong>
                    {{ prop.value }}
                  </li>
                </ul>
              </td>
              <td class="text-center">
                <button class="btn btn-warning btn-sm mr-1" @click="editRoomType(roomType)">
                  <SquarePen />
                </button>
                <button class="btn btn-danger btn-sm" @click="deleteRoomType(roomType.id)">
                  <Trash2 />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="roomTypeModal" tabindex="-1" aria-labelledby="roomTypeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="roomTypeModalLabel">{{ isEditing ? 'Edit room type' : 'Add room type' }}
            </h5>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveRoomType">
              <div class="mb-3">
                <label for="roomTypeName" class="form-label">Room type</label>
                <input type="text" class="form-control" id="roomTypeName" v-model="currentRoomType.name" required>
              </div>
              <div class="mb-3">
                <label for="roomTypePrice" class="form-label">Price</label>
                <input type="number" class="form-control" id="roomTypePrice" v-model="currentRoomType.price" required
                  min="0">
              </div>

              <h6 class="mt-4 mb-2">Properties of room type:</h6>
              <div v-for="(prop, index) in currentRoomType.properties" :key="index"
                class="row g-2 mb-2 align-items-center border p-2 rounded">
                <div class="col-md-5">
                  <select class="form-select form-select-sm" v-model="prop.property_id" required
                    @change="onPropertyChange(index)">
                    <option value="" disabled>Select property</option>
                    <option v-for="property in propertiesList" :key="property.id" :value="property.id"
                      :disabled="currentRoomType.properties.some((p, idx) => p.property_id === property.id && idx !== index)">
                      {{ property.name }}
                    </option>
                  </select>
                </div>
                <div class="col-md-5">
                  <input type="text" class="form-control form-control-sm" v-model="prop.value" placeholder="value"
                    required>
                </div>
                <div class="col-md-2 text-end">
                  <button type="button" class="btn btn-outline-danger btn-sm" @click="removeProperty(index)">
                    <X />
                  </button>
                </div>
              </div>
              <button type="button" class="btn btn-outline-secondary btn-sm mt-2" @click="addProperty"
                :disabled="currentRoomType.properties.length >= propertiesList.length">
                <i class="bi bi-plus-circle"></i> Add property
              </button>

              <div class="modal-footer mt-4">
                <button type="button" class="btn btn-secondary" @click="closeModal">Close</button>
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-save"></i> Save
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.form-select {
  background-color: #fff;
  border: 1px solid #ced4da;
  border-radius: 0.25rem;
  padding: 0.375rem 2.25rem 0.375rem 0.75rem;
  font-size: 1rem;
  line-height: 1;
  color: #495057;
  cursor: pointer;
  width: 100%;
}

.form-select:focus {
  border-color: #80bdff;
  outline: 0;
  box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
}

.form-select:disabled {
  background-color: #e9ecef;
  opacity: 1;
}

.form-select:not(:disabled):hover {
  border-color: #6c757d;
}
</style>
