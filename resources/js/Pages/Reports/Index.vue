<template>
  <AuthenticatedLayout>
    <Head title="Reports & Analytics" />

    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="font-semibold text-2xl text-gray-800 leading-tight">Reports & Analytics</h2>
          <p class="text-gray-600 mt-1">Comprehensive business insights and data analysis</p>
        </div>
        <div class="flex items-center space-x-2">
          <div class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
            📊 Analytics Dashboard
          </div>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Quick Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-blue-100 text-sm font-medium">Total Sales</p>
                <p class="text-2xl font-bold">$12,450</p>
                <p class="text-blue-100 text-xs">+12% this month</p>
              </div>
              <div class="bg-blue-400 rounded-lg p-3">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-green-100 text-sm font-medium">Total Purchases</p>
                <p class="text-2xl font-bold">$8,230</p>
                <p class="text-green-100 text-xs">+8% this month</p>
              </div>
              <div class="bg-green-400 rounded-lg p-3">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 2L3 7v11a1 1 0 001 1h12a1 1 0 001-1V7l-7-5zM8 15a1 1 0 01-2 0V9a1 1 0 012 0v6zm4 0a1 1 0 01-2 0V9a1 1 0 012 0v6z" clip-rule="evenodd"/>
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-purple-100 text-sm font-medium">Net Profit</p>
                <p class="text-2xl font-bold">$4,220</p>
                <p class="text-purple-100 text-xs">+15% this month</p>
              </div>
              <div class="bg-purple-400 rounded-lg p-3">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-orange-100 text-sm font-medium">Outstanding</p>
                <p class="text-2xl font-bold">$1,890</p>
                <p class="text-orange-100 text-xs">-5% this month</p>
              </div>
              <div class="bg-orange-400 rounded-lg p-3">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Reports Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Sales Reports Card -->
          <Link :href="route('reports.sales')" class="group">
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100 overflow-hidden">
              <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6">
                <div class="flex items-center justify-between mb-4">
                  <div class="bg-blue-500 rounded-lg p-3 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                    </svg>
                  </div>
                  <span class="text-blue-600 text-sm font-medium">Sales</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Sales Reports</h3>
                <p class="text-gray-600 text-sm mb-4">Analyze sales performance, trends, and revenue patterns</p>
                <div class="flex items-center text-blue-600 text-sm font-medium">
                  <span>View Reports</span>
                  <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                  </svg>
                </div>
              </div>
            </div>
          </Link>

          <!-- Purchase Reports Card -->
          <Link :href="route('reports.purchases')" class="group">
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100 overflow-hidden">
              <div class="bg-gradient-to-br from-green-50 to-green-100 p-6">
                <div class="flex items-center justify-between mb-4">
                  <div class="bg-green-500 rounded-lg p-3 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 2L3 7v11a1 1 0 001 1h12a1 1 0 001-1V7l-7-5zM8 15a1 1 0 01-2 0V9a1 1 0 012 0v6zm4 0a1 1 0 01-2 0V9a1 1 0 012 0v6z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <span class="text-green-600 text-sm font-medium">Purchases</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Purchase Reports</h3>
                <p class="text-gray-600 text-sm mb-4">Track purchase orders, supplier performance, and inventory costs</p>
                <div class="flex items-center text-green-600 text-sm font-medium">
                  <span>View Reports</span>
                  <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                  </svg>
                </div>
              </div>
            </div>
          </Link>

          <!-- Profit Loss Card -->
          <Link :href="route('reports.profit-loss')" class="group">
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100 overflow-hidden">
              <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-6">
                <div class="flex items-center justify-between mb-4">
                  <div class="bg-purple-500 rounded-lg p-3 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <span class="text-purple-600 text-sm font-medium">Analytics</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Profit & Loss</h3>
                <p class="text-gray-600 text-sm mb-4">Comprehensive financial analysis and profitability insights</p>
                <div class="flex items-center text-purple-600 text-sm font-medium">
                  <span>View Reports</span>
                  <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                  </svg>
                </div>
              </div>
            </div>
          </Link>

          <!-- Customer Dues Card -->
          <Link :href="route('reports.customer-dues')" class="group">
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100 overflow-hidden">
              <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-6">
                <div class="flex items-center justify-between mb-4">
                  <div class="bg-orange-500 rounded-lg p-3 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <span class="text-orange-600 text-sm font-medium">Dues</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Customer Dues</h3>
                <p class="text-gray-600 text-sm mb-4">Monitor outstanding payments and customer credit management</p>
                <div class="flex items-center text-orange-600 text-sm font-medium">
                  <span>View Reports</span>
                  <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                  </svg>
                </div>
              </div>
            </div>
          </Link>
        </div>

        <!-- Additional Features Section -->
        <div class="mt-12 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-8">
          <div class="text-center">
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Advanced Analytics</h3>
            <p class="text-gray-600 mb-6">Get deeper insights with our comprehensive reporting tools</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="text-center">
                <div class="bg-white rounded-lg p-4 shadow-sm">
                  <div class="text-2xl mb-2">📈</div>
                  <h4 class="font-medium text-gray-800">Trend Analysis</h4>
                  <p class="text-sm text-gray-600">Track performance over time</p>
                </div>
              </div>
              <div class="text-center">
                <div class="bg-white rounded-lg p-4 shadow-sm">
                  <div class="text-2xl mb-2">🎯</div>
                  <h4 class="font-medium text-gray-800">Custom Filters</h4>
                  <p class="text-sm text-gray-600">Filter by date, product, customer</p>
                </div>
              </div>
              <div class="text-center">
                <div class="bg-white rounded-lg p-4 shadow-sm">
                  <div class="text-2xl mb-2">📊</div>
                  <h4 class="font-medium text-gray-800">Export Data</h4>
                  <p class="text-sm text-gray-600">Download reports in multiple formats</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
</script>
