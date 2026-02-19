<template>
  <Head title="Bills" />
  <div class="min-h-screen bg-gray-100 py-8 px-4 md:px-8">
    <!-- Header -->
    <Header />

    <div class="max-w-7xl mx-auto mt-8">
      <!-- Back Button -->
      <Link href="/dashboard" class="inline-flex items-center gap-2 px-4 py-2 mb-6 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Back to Dashboard
      </Link>  

      <!-- Title -->
      <h1 class="text-3xl font-bold text-gray-900 mb-8">All Bills</h1>

      <!-- Search Card -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
          <div class="w-full md:w-96">
            <label for="search-filter" class="block text-sm font-medium text-gray-700 mb-2">
              Search Bill:
            </label>
            <input
              id="search-filter"
              v-model="searchQuery"
              type="text"
              placeholder="Search by Order ID or Customer Name/Phone..."
              @input="handleSearch"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div class="text-gray-600 text-sm md:text-base">
            <p><strong>Total Bills:</strong> {{ bills.total }}</p>
          </div>
        </div>
      </div>

      <!-- Bills Table -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div v-if="loading" class="p-8 text-center">
          <p class="text-gray-500 text-lg">Loading bills...</p>
        </div>

        <div v-else-if="bills.data.length === 0" class="p-8 text-center">
          <p class="text-gray-500 text-lg">No bills found</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">#</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Order ID</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Date</th>
                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr
                v-for="(bill, index) in bills.data"
                :key="bill.id"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4 text-sm text-gray-900">{{ index + 1 + (bills.current_page - 1) * bills.per_page }}</td>
                <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ bill.order_id }}</td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ bill.created_at }}</td>
                <td class="px-6 py-4 text-center">
                  <Link :href="`/bill-view/${bill.id}`" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    View
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="bills.last_page > 1" class="mt-6 flex justify-center gap-2">
        <Link
          v-if="bills.current_page > 1"
          :href="`/bill-view?page=${bills.current_page - 1}&search=${searchQuery}`"
          class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
        >
          Previous
        </Link>

        <div class="flex gap-1">
          <Link
            v-for="page in getPageNumbers()"
            :key="page"
            :href="`/bill-view?page=${page}&search=${searchQuery}`"
            :class="['px-4 py-2 rounded-lg', page === bills.current_page ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 hover:bg-gray-50']"
          >
            {{ page }}
          </Link>
        </div>

        <Link
          v-if="bills.current_page < bills.last_page"
          :href="`/bill-view?page=${bills.current_page + 1}&search=${searchQuery}`"
          class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
        >
          Next
        </Link>
      </div>
    </div>

    <!-- Footer -->
    <Footer />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Header from '@/Components/custom/Header.vue';
import Footer from '@/Components/custom/Footer.vue';

const props = defineProps({
  bills: {
    type: Object,
    required: true,
  },
  search: {
    type: String,
    default: '',
  },
});

const loading = ref(false);
const searchQuery = ref(props.search);
let searchTimeout = null;

// Handle search with debounce
const handleSearch = () => {
  clearTimeout(searchTimeout);
  loading.value = true;
  searchTimeout = setTimeout(() => {
    window.location.href = `/bill-view?search=${searchQuery.value}`;
  }, 500);
};

// Get page numbers for pagination
const getPageNumbers = () => {
  const pages = [];
  const maxPages = 5;
  const startPage = Math.max(1, props.bills.current_page - Math.floor(maxPages / 2));
  const endPage = Math.min(props.bills.last_page, startPage + maxPages - 1);

  for (let i = startPage; i <= endPage; i++) {
    pages.push(i);
  }
  return pages;
};
</script>

<style scoped>
@media (max-width: 768px) {
  table {
    font-size: 0.875rem;
  }

  th,
  td {
    padding: 0.5rem !important;
  }
}
</style>
