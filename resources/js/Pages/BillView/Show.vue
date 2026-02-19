<template>
  <Head title="Bill Details" />
  <div class="min-h-screen bg-gray-100 py-8 px-4 md:px-8">
    <!-- Header -->
    <Header />

    <div class="max-w-4xl mx-auto mt-8">
      <!-- Back Button -->
      <Link href="/bill-view" class="inline-flex items-center gap-2 px-4 py-2 mb-6 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Back to Bills
      </Link>

      <!-- Bill Header Card -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 pb-6 border-b border-gray-200">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Bill #{{ bill.order_id }}</h1>
            <p class="text-gray-600 mt-1">{{ bill.created_at }}</p>
          </div>
          <div class="text-right">
            <p class="text-sm text-gray-600">Payment Method:</p>
            <span class="px-3 py-1 rounded-full text-sm font-semibold" :class="getPaymentMethodBadge(bill.payment_method)">
              {{ bill.payment_method }}
            </span>
          </div>
        </div>

        <!-- Customer & Employee Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Customer Info -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Customer Information</h3>
            <div class="space-y-2 text-gray-700">
              <p><strong>Name:</strong> {{ bill.customer ? bill.customer.name : 'N/A' }}</p>
              <p><strong>Phone:</strong> {{ bill.customer ? bill.customer.phone : 'N/A' }}</p>
              <p v-if="bill.customer"><strong>Email:</strong> {{ bill.customer.email }}</p>
            </div>
          </div>

          <!-- Employee Info -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Staff Information</h3>
            <div class="space-y-2 text-gray-700">
              <p><strong>Handled by:</strong> {{ bill.employee ? bill.employee.name : 'N/A' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Bill Items Table -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
        <div class="p-6 bg-gray-50 border-b border-gray-200">
          <h2 class="text-xl font-semibold text-gray-900">Bill Items</h2>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">#</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Product Name</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Product Code</th>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Unit Price</th>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Quantity</th>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Total Price</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="(item, index) in bill.items" :key="item.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 text-sm text-gray-900">{{ index + 1 }}</td>
                <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ item.product_name }}</td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ item.product_code }}</td>
                <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ formatCurrency(item.unit_price) }}</td>
                <td class="px-6 py-4 text-sm text-gray-900 text-right font-semibold">{{ item.quantity }}</td>
                <td class="px-6 py-4 text-sm text-gray-900 text-right font-semibold">{{ formatCurrency(item.total_price) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Bill Summary -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Bill Summary</h2>

        <div class="space-y-3 border-t border-gray-200 pt-6">
          <div class="flex justify-between items-center">
            <span class="text-gray-700">Subtotal:</span>
            <span class="text-lg font-semibold text-gray-900">{{ formatCurrency(bill.subtotal) }}</span>
          </div>

          <div v-if="bill.discount > 0" class="flex justify-between items-center">
            <span class="text-gray-700">Discount:</span>
            <span class="text-lg font-semibold text-red-600">-{{ formatCurrency(bill.discount) }}</span>
          </div>

          <div v-if="bill.custom_discount > 0" class="flex justify-between items-center">
            <span class="text-gray-700">Custom Discount:</span>
            <span class="text-lg font-semibold text-red-600">-{{ formatCurrency(bill.custom_discount) }}</span>
          </div>

          <div class="border-t border-gray-200 pt-3 flex justify-between items-center">
            <span class="text-lg font-semibold text-gray-900">Total Amount:</span>
            <span class="text-2xl font-bold text-blue-600">{{ formatCurrency(bill.total_amount) }}</span>
          </div>

          <div v-if="bill.cash" class="border-t border-gray-200 pt-3 flex justify-between items-center">
            <span class="text-gray-700">Cash Received:</span>
            <span class="text-lg font-semibold text-gray-900">{{ formatCurrency(bill.cash) }}</span>
          </div>
        </div>

        <!-- Print Button -->
        <div class="mt-8 flex gap-3 pt-6 border-t border-gray-200">
          <button
            @click="printBill"
            class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4H9a2 2 0 00-2 2v2a2 2 0 002 2h10a2 2 0 002-2v-2a2 2 0 00-2-2h-2m-4-4V9m0 4v6m0-10V5m0 4V3"></path>
            </svg>
            Print Bill
          </button>

          <Link
            href="/bill-view"
            class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-medium"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Close
          </Link>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <Footer />
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Header from '@/Components/custom/Header.vue';
import Footer from '@/Components/custom/Footer.vue';

const props = defineProps({
  bill: {
    type: Object,
    required: true,
  },
});

// Format currency
const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'LKR',
  }).format(value ?? 0);
};

// Get payment method badge styling
const getPaymentMethodBadge = (method) => {
  const badges = {
    'Cash': 'bg-green-100 text-green-800',
    'Card': 'bg-blue-100 text-blue-800',
    'Check': 'bg-yellow-100 text-yellow-800',
    'Transfer': 'bg-purple-100 text-purple-800',
  };
  return badges[method] || 'bg-gray-100 text-gray-800';
};

// Print bill
const printBill = () => {
  window.print();
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

@media print {
  /* Hide non-printable elements */
  button,
  a {
    display: none;
  }
}
</style>
