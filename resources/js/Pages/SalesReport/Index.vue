<template>
  <Head title="Sales Report" />
  <div class="min-h-screen bg-gray-100 py-8 px-4 md:px-8">
    <!-- Header -->
    <Header />

    <div class="max-w-6xl mx-auto mt-8">
      <!-- Back Button -->
      <Link href="/dashboard" class="inline-flex items-center gap-2 px-4 py-2 mb-6 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Back to Dashboard
      </Link>
      <!-- Title -->
      <h1 class="text-3xl font-bold text-gray-900 mb-8">Sales Report</h1>

      <!-- Date Filter Card -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
          <div class="w-full md:w-auto">
            <label for="date-filter" class="block text-sm font-medium text-gray-700 mb-2">
              Select Date:
            </label>
            <input
              id="date-filter"
              v-model="selectedDate"
              type="date"
              @change="fetchSalesData"
              class="w-full md:w-48 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div class="text-gray-600 text-sm md:text-base">
            <p><strong>Total Items Sold:</strong> {{ totalQuantity }}</p>
          </div>
        </div>
      </div>

        <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Total Sales Price Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm uppercase tracking-wide">Total Sales Price</p>
              <p class="text-3xl font-bold text-blue-600 mt-2">{{ formatCurrency(totalSalesPrice) }}</p>
            </div>
            <div class="p-3 bg-blue-100 rounded-full">
              <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
          </div>
        </div>

        <!-- Number of Sales Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm uppercase tracking-wide">Number of Sales</p>
              <p class="text-3xl font-bold text-green-600 mt-2 text-center">{{ numberOfSales }}</p>
            </div>
            <div class="p-3 bg-green-100 rounded-full">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Sales Table -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div v-if="loading" class="p-8 text-center">
          <p class="text-gray-500 text-lg">Loading sales data...</p>
        </div>

        <div v-else-if="salesData.length === 0" class="p-8 text-center">
          <p class="text-gray-500 text-lg">No sales data available for {{ formattedDate }}</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">#</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Item Name</th>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Quantity Sold</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr
                v-for="(item, index) in salesData"
                :key="item.product_id"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4 text-sm text-gray-900">{{ index + 1 }}</td>
                <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ item.product_name }}</td>
                
                <td class="px-6 py-4 text-sm text-gray-900 text-right font-semibold">
                  {{ item.total_quantity }}
                </td>
                
              </tr>
            </tbody>
            <tfoot class="bg-gray-50 border-t-2 border-gray-200">
              <tr>
                <td colspan="2" class="px-6 py-4 text-right text-sm font-semibold text-gray-900">
                  Totals:
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 text-right font-bold">
                  {{ totalQuantity }}
                </td>
                
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <Footer />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Header from '@/Components/custom/Header.vue';
import Footer from '@/Components/custom/Footer.vue';
import axios from 'axios';

const props = defineProps({
  salesData: {
    type: Array,
    default: () => [],
  },
  selectedDate: {
    type: String,
    default: '',
  },
  totalSalesPrice: {
    type: Number,
    default: 0,
  },
  numberOfSales: {
    type: Number,
    default: 0,
  },
});

const loading = ref(false);
const salesData = ref(props.salesData);
const selectedDate = ref(props.selectedDate);
const totalSalesPrice = ref(props.totalSalesPrice);
const numberOfSales = ref(props.numberOfSales);

// Format date for display
const formattedDate = computed(() => {
  if (!selectedDate.value) return '';
  const date = new Date(selectedDate.value);
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
});

// Calculate total quantity
const totalQuantity = computed(() => {
  return salesData.value.reduce((sum, item) => sum + item.total_quantity, 0);
});

// Format currency
const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'LKR',
  }).format(value ?? 0);
};

// Fetch sales data for selected date
const fetchSalesData = async () => {
  loading.value = true;
  try {
    const response = await axios.post('/api/sales-report', {
      date: selectedDate.value,
    });

    if (response.data.success) {
      salesData.value = response.data.data;
      totalSalesPrice.value = response.data.totalSalesPrice ?? 0;
      numberOfSales.value = response.data.numberOfSales ?? 0;
    }
  } catch (error) {
    console.error('Error fetching sales data:', error);
  } finally {
    loading.value = false;
  }
};

// Initialize component
onMounted(() => {
  if (!selectedDate.value) {
    const today = new Date();
    selectedDate.value = today.toISOString().split('T')[0];
  }
});
</script>

<style scoped>
/* Table responsive styles */
@media (max-width: 768px) {
  table {
    font-size: 0.875rem;
  }

  th,
  td {
    padding: 0.75rem 0.5rem !important;
  }

  .flex {
    flex-direction: column;
  }
}
</style>
