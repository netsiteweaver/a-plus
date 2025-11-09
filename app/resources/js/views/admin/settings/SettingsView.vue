<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Settings</h1>
                <p class="text-sm text-slate-500">Manage site-wide configuration</p>
            </div>
            <button
                @click="saveSettings"
                :disabled="saving"
                class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-sky-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
                {{ saving ? 'Saving...' : 'Save Changes' }}
            </button>
        </div>

        <div v-if="loading" class="flex items-center justify-center py-12">
            <div class="text-sm text-slate-500">Loading settings...</div>
        </div>

        <div v-else class="space-y-6">
            <!-- Business Settings -->
            <div class="rounded-lg border border-slate-200 bg-white">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-800">Business Settings</h2>
                    <p class="text-sm text-slate-500">Configure business-related settings including currency</p>
                </div>
                <div class="space-y-4 p-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Currency -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">
                                Currency Code
                                <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="settingsData.business.currency"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20"
                            >
                                <option value="MUR">MUR - Mauritian Rupee</option>
                                <option value="USD">USD - US Dollar</option>
                                <option value="EUR">EUR - Euro</option>
                                <option value="GBP">GBP - British Pound</option>
                                <option value="JPY">JPY - Japanese Yen</option>
                                <option value="AUD">AUD - Australian Dollar</option>
                                <option value="CAD">CAD - Canadian Dollar</option>
                                <option value="CHF">CHF - Swiss Franc</option>
                                <option value="CNY">CNY - Chinese Yuan</option>
                                <option value="INR">INR - Indian Rupee</option>
                                <option value="BDT">BDT - Bangladeshi Taka</option>
                                <option value="PKR">PKR - Pakistani Rupee</option>
                                <option value="AED">AED - UAE Dirham</option>
                                <option value="SAR">SAR - Saudi Riyal</option>
                            </select>
                            <p class="mt-1 text-xs text-slate-500">Currency code for prices (ISO 4217)</p>
                        </div>

                        <!-- Currency Symbol -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">
                                Currency Symbol
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="settingsData.business.currency_symbol"
                                type="text"
                                placeholder="$"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20"
                            />
                            <p class="mt-1 text-xs text-slate-500">Symbol to display for currency</p>
                        </div>

                        <!-- Timezone -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Timezone</label>
                            <select
                                v-model="settingsData.business.timezone"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20"
                            >
                                <option value="UTC">UTC</option>
                                <option value="America/New_York">America/New_York</option>
                                <option value="America/Los_Angeles">America/Los_Angeles</option>
                                <option value="Europe/London">Europe/London</option>
                                <option value="Europe/Paris">Europe/Paris</option>
                                <option value="Asia/Tokyo">Asia/Tokyo</option>
                                <option value="Asia/Dubai">Asia/Dubai</option>
                                <option value="Asia/Dhaka">Asia/Dhaka</option>
                                <option value="Asia/Karachi">Asia/Karachi</option>
                                <option value="Asia/Kolkata">Asia/Kolkata</option>
                                <option value="Australia/Sydney">Australia/Sydney</option>
                            </select>
                            <p class="mt-1 text-xs text-slate-500">Default timezone for the application</p>
                        </div>

                        <!-- Business Email -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Business Email</label>
                            <input
                                v-model="settingsData.business.email"
                                type="email"
                                placeholder="info@example.com"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20"
                            />
                            <p class="mt-1 text-xs text-slate-500">Primary business contact email</p>
                        </div>

                        <!-- Business Phone -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Business Phone</label>
                            <input
                                v-model="settingsData.business.phone_number"
                                type="tel"
                                placeholder="+1 234 567 8900"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20"
                            />
                            <p class="mt-1 text-xs text-slate-500">Primary business contact phone</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Company Settings -->
            <div class="rounded-lg border border-slate-200 bg-white">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-800">Company Information</h2>
                    <p class="text-sm text-slate-500">Configure company branding and information</p>
                </div>
                <div class="space-y-4 p-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Website Name -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">
                                Website Name
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="settingsData.company.website_name"
                                type="text"
                                placeholder="A Plus Technology"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20"
                            />
                            <p class="mt-1 text-xs text-slate-500">Display name for your website</p>
                        </div>

                        <!-- Tagline -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Tagline</label>
                            <input
                                v-model="settingsData.company.tagline"
                                type="text"
                                placeholder="Your company tagline"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20"
                            />
                            <p class="mt-1 text-xs text-slate-500">Brief company tagline</p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Description</label>
                        <textarea
                            v-model="settingsData.company.description"
                            rows="3"
                            placeholder="Company description"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20"
                        ></textarea>
                        <p class="mt-1 text-xs text-slate-500">Brief description of your company</p>
                    </div>
                </div>
            </div>

            <!-- Currency Preview -->
            <div class="rounded-lg border border-sky-200 bg-sky-50 p-6">
                <h3 class="mb-4 text-sm font-semibold text-sky-900">Currency Preview</h3>
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-lg bg-white p-4">
                        <p class="mb-1 text-xs text-slate-500">Sample Price</p>
                        <p class="text-2xl font-bold text-slate-900">{{ formatPreview(999.99) }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-4">
                        <p class="mb-1 text-xs text-slate-500">Large Amount</p>
                        <p class="text-2xl font-bold text-slate-900">{{ formatPreview(1234567) }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-4">
                        <p class="mb-1 text-xs text-slate-500">Small Amount</p>
                        <p class="text-2xl font-bold text-slate-900">{{ formatPreview(9.99) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success message -->
        <div
            v-if="showSuccess"
            class="fixed bottom-4 right-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 shadow-lg"
        >
            Settings saved successfully!
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { api } from '@/services/http';

const loading = ref(true);
const saving = ref(false);
const showSuccess = ref(false);
const settingsData = reactive({
    business: {
        currency: 'MUR',
        currency_symbol: 'Rs',
        timezone: 'UTC',
        email: '',
        phone_number: '',
    },
    company: {
        website_name: '',
        tagline: '',
        description: '',
    },
});

const loadSettings = async () => {
    loading.value = true;
    try {
        const { data } = await api.get('/admin/settings');
        
        // Group settings by group
        const grouped = {};
        for (const group in data) {
            grouped[group] = {};
            data[group].forEach((setting) => {
                const value = setting.value;
                // Unwrap single-value arrays
                if (setting.type !== 'json' && Array.isArray(value) && value.length === 1) {
                    grouped[group][setting.key] = value[0] ?? null;
                } else {
                    grouped[group][setting.key] = value;
                }
            });
        }

        // Populate settingsData
        if (grouped.business) {
            settingsData.business = { ...settingsData.business, ...grouped.business };
        }
        if (grouped.company) {
            settingsData.company = { ...settingsData.company, ...grouped.company };
        }
    } catch (error) {
        console.error('Failed to load settings:', error);
    } finally {
        loading.value = false;
    }
};

const saveSettings = async () => {
    saving.value = true;
    try {
        const settingsToUpdate = [];

        // Business settings
        for (const key in settingsData.business) {
            settingsToUpdate.push({
                key,
                value: settingsData.business[key],
            });
        }

        // Company settings
        for (const key in settingsData.company) {
            settingsToUpdate.push({
                key,
                value: settingsData.company[key],
            });
        }

        await api.put('/admin/settings/bulk', { settings: settingsToUpdate });
        
        showSuccess.value = true;
        setTimeout(() => {
            showSuccess.value = false;
        }, 3000);

        // Reload settings to reflect changes
        await loadSettings();
    } catch (error) {
        console.error('Failed to save settings:', error);
        alert('Failed to save settings. Please try again.');
    } finally {
        saving.value = false;
    }
};

const formatPreview = (value) => {
    try {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: settingsData.business.currency || 'USD',
            maximumFractionDigits: 2,
        }).format(value);
    } catch (error) {
        return `${settingsData.business.currency_symbol || '$'}${value.toFixed(2)}`;
    }
};

onMounted(loadSettings);
</script>

