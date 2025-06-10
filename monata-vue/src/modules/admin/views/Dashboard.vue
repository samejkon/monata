<template>
  <div>
    <div class="row">
      <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
          <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Revenue Overview</h6>
            <div class="btn-group" role="group" aria-label="Revenue type">
              <button type="button" class="btn btn-sm btn-outline-primary" :class="{ 'active': revenueType === 'day' }"
                @click="setRevenueType('day')">Day</button>
              <button type="button" class="btn btn-sm btn-outline-primary" :class="{ 'active': revenueType === 'week' }"
                @click="setRevenueType('week')">Week</button>
              <button type="button" class="btn btn-sm btn-outline-primary"
                :class="{ 'active': revenueType === 'month' }" @click="setRevenueType('month')">Month</button>
            </div>
          </div>
          <div class="card-body">
            <div class="chart-area" style="height: 320px;">
              <RevenueChart v-if="chartData.datasets.length" :chart-data="chartData" />
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-lg-5">
        <div class="row">
          <div class="col-xl-12 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tasks
                    </div>
                    <div class="row no-gutters align-items-center">
                      <div class="col-auto">
                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">50%</div>
                      </div>
                      <div class="col">
                        <div class="progress progress-sm mr-2">
                          <div class="progress-bar bg-info" role="progressbar" style="width: 50%" aria-valuenow="50"
                            aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-12 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                      Pending Requests</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ totalBookingPending }}</div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-comments fa-2x text-gray-300"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted, computed } from 'vue';
import RevenueChart from '@/modules/admin/components/RevenueChart.vue';
// @ts-ignore
import { ChartData } from 'chart.js';
import { api } from '@/modules/admin/lib/axios';

export default defineComponent({
  name: 'DashboardView',
  components: {
    RevenueChart,
  },
  setup() {
    const revenueType = ref('day');
    const rawData = ref<any[]>([]);
    const totalBookingPending = ref(0);

    const getRevenue = async (type: string) => {
      try {
        const response = await api.get('/revenue', { params: { type } });
        rawData.value = response.data;
      } catch (error) {
        console.error('Error fetching revenue data:', error);
        rawData.value = [];
      }
    };

    const getTotalBookingPending = async () => {
      try {
        const response = await api.get('/booking/pending/count');
        totalBookingPending.value = response.data.count;
      } catch (error) {
        console.error('Error fetching pending bookings count:', error);
        totalBookingPending.value = 0;
      }
    };

    const setRevenueType = (type: string) => {
      revenueType.value = type;
      getRevenue(type);
    }

    onMounted(() => {
      getRevenue(revenueType.value);
      getTotalBookingPending();
    });

    const chartData = computed<ChartData<'line'>>(() => {
      if (!rawData.value || rawData.value.length === 0) {
        return { labels: [], datasets: [] };
      }

      const labels = rawData.value.map(item => item.revenue_date || item.revenue_week || item.revenue_month);
      const data = rawData.value.map(item => item.revenue);

      return {
        labels,
        datasets: [
          {
            label: 'Revenue',
            backgroundColor: 'rgba(78, 115, 223, 0.05)',
            borderColor: 'rgba(78, 115, 223, 1)',
            pointRadius: 3,
            pointBackgroundColor: "rgba(78, 115, 223, 1)",
            pointBorderColor: "rgba(78, 115, 223, 1)",
            pointHoverRadius: 3,
            pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
            pointHoverBorderColor: "rgba(78, 115, 223, 1)",
            pointHitRadius: 10,
            pointBorderWidth: 2,
            data: data,
          },
        ],
      };
    });

    return {
      revenueType,
      setRevenueType,
      chartData,
      totalBookingPending,
    };
  }
});
</script>

<style scoped></style>
