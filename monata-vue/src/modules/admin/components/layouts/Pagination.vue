<template>
  <div class="d-flex justify-content-center align-items-center gap-3 mt-4 flex-wrap">
    <button class="btn btn-sm btn-outline-primary" 
            @click="changePage(currentPage - 1)"
            :disabled="currentPage <= 1">
      <i class="fa-solid fa-angle-left"></i> Previous
    </button>
    <p class="m-0 small">Page {{ currentPage }} of {{ totalPages }}</p>
    <button class="btn btn-sm btn-outline-primary"
            @click="changePage(currentPage + 1)" 
            :disabled="currentPage >= totalPages">
      Next <i class="fa-solid fa-angle-right"></i>
    </button>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    currentPage: {
      type: Number,
      required: true
    },
    totalPages: {
      type: Number,
      required: true
    }
  },
  emits: ['update:currentPage'],
  setup(props, { emit }) {
    const changePage = (page: number) => {
      if (page >= 1 && page <= props.totalPages) {
        emit('update:currentPage', page);
      }
    };

    return {
      changePage
    };
  }
});
</script>