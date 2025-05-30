<script setup>
import axios from 'axios';
import { Plus, SquarePen, Trash2 } from 'lucide-vue-next';
import { onMounted, ref, reactive } from 'vue';

const url = import.meta.env.VITE_API_URL;

const records = ref([]);
const pagination = ref({});
const creatingRecord = ref(false);
const editingRecord = ref(null);

const queryParams = reactive({
  page: 1,
  per_page: 10,
  keyword: ''
});

const fetchData = async (page = 1) => {
  queryParams.page = page;
  try {
    const response = await axios.get(`${url}/admin/room-types`, {
      params: queryParams
    });
    records.value = response.data?.data || [];
    pagination.value = response.data?.meta || {};
  } catch (error) {
    console.error('Error fetching room types', error);
    records.value = [];
  }
};

const deleteRecord = async (id) => {
  if (!confirm('Are you sure you want to delete this room type?')) return;

  try {
    await axios.delete(`${url}/admin/room-types/${id}`);
    await fetchData(queryParams.page);
  } catch (error) {
    console.error('Failed to delete room type', error);
  }
};

onMounted(() => {
  fetchData();
});
</script>


<template>
  <div class="card shadow mb-4">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary">Manage Room Types</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th rowspan="2">#</th>
              <th rowspan="2">Name</th>
              <th rowspan="2">Price</th>
              <th colspan="2" class="d-none d-sm-table-cell">Properties</th>
              <th rowspan="2" width="10%" class="text-center">
                <button class="btn btn-primary btn-sm" @click="startCreating" v-if="!creatingRecord">
                  <Plus />
                </button>
              </th>
            </tr>
            <tr>
              <th>Property</th>
              <th>Quantity</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(record, index) in records" :key="record.id">
              <tr v-for="(property, pIdx) in record.properties.length ? record.properties : [null]" :key="pIdx">
                <template v-if="pIdx === 0">
                  <td :rowspan="record.properties.length || 1">{{ index + 1 }}</td>
                  <td :rowspan="record.properties.length || 1" class="justify-content-center">{{ record.name }}</td>
                  <td :rowspan="record.properties.length || 1">{{ record.price }}</td>
                </template>
                <td class="d-none d-sm-table-cell">
                  {{ property ? property.name : '-' }}
                </td>
                <td class="d-none d-sm-table-cell">
                  {{ property ? property.value : '-' }}
                </td>
                <template v-if="pIdx === 0">
                  <td :rowspan="record.properties.length || 1">
                    <div class="text-center">
                      <button class="btn btn-warning btn-sm mr-1" @click="startEditing(record)">
                        <SquarePen />
                      </button>
                      <button class="btn btn-danger btn-sm" @click="deleteRecord(record.id)">
                        <Trash2 />
                      </button>
                    </div>
                  </td>
                </template>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
