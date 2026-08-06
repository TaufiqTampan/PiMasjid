<script setup>
import { ref, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue';
import { CheckCircleIcon, PhotoIcon, MapPinIcon } from '@heroicons/vue/24/outline';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Fix Leaflet marker icons
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconUrl: markerIcon,
    iconRetinaUrl: markerIcon2x,
    shadowUrl: markerShadow,
});

const props = defineProps({
    settings: Object
});

const groups = {
    general: 'Umum',
    hero: 'Beranda (Hero)',
    display: 'TV Display Digital',
    contact: 'Kontak',
    social: 'Sosial Media',
    about: 'Tentang Kami',
    report: 'Laporan & Tanda Tangan',
    location: 'Lokasi (Prayer Times)',
    donation: 'Donasi & QRIS',
    footer: 'Footer'
};

const form = useForm({
    settings: []
});

// Map instance
let map = null;
let marker = null;
const selectedTabIndex = ref(0);
const mapInitialized = ref(false);

// Helper to flatten settings for submission
const prepareForm = () => {
    let flatSettings = [];
    Object.values(props.settings).forEach(groupItems => {
        groupItems.forEach(item => {
            flatSettings.push({
                key: item.key,
                value: item.value,
                type: item.type,
                file: null // For image uploads
            });
        });
    });
    form.settings = flatSettings;
};

// Initialize form
prepareForm();

// Initialize map manually with button
const initializeMap = () => {
    console.log('Initializing map...');
    
    // Destroy existing map if any
    if (map) {
        console.log('Removing existing map');
        map.remove();
        map = null;
    }
    
    const mapContainer = document.getElementById('location-map');
    if (!mapContainer) {
        console.error('Map container not found!');
        alert('Error: Map container not found. Please refresh the page.');
        return;
    }
    
    console.log('Map container found:', mapContainer);
    
    try {
        // Get current lat/lng from settings
        const latSetting = form.settings.find(s => s.key === 'location_latitude');
        const lngSetting = form.settings.find(s => s.key === 'location_longitude');
        
        const lat = parseFloat(latSetting?.value || -6.200000);
        const lng = parseFloat(lngSetting?.value || 106.816666);
        
        console.log('Coordinates:', lat, lng);
        
        // Initialize map
        map = L.map('location-map').setView([lat, lng], 13);
        console.log('Map instance created');
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);
        console.log('Tiles added');
        
        // Add marker
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        console.log('Marker added');
        
        // Update coordinates when marker is dragged
        marker.on('dragend', function(e) {
            const position = marker.getLatLng();
            updateCoordinates(position.lat, position.lng);
        });
        
        // Update coordinates when map is clicked
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            updateCoordinates(e.latlng.lat, e.latlng.lng);
        });
        
        // Force map to refresh its size
        setTimeout(() => {
            if (map) {
                map.invalidateSize();
                console.log('Map size invalidated');
            }
        }, 100);
        
        mapInitialized.value = true;
        console.log('Map initialization complete!');
        
    } catch (error) {
        console.error('Error initializing map:', error);
        alert('Error initializing map: ' + error.message);
    }
};

// Update coordinate fields
const updateCoordinates = (lat, lng) => {
    const latSetting = form.settings.find(s => s.key === 'location_latitude');
    const lngSetting = form.settings.find(s => s.key === 'location_longitude');
    
    if (latSetting) latSetting.value = lat.toFixed(6);
    if (lngSetting) lngSetting.value = lng.toFixed(6);
    
    console.log('Coordinates updated:', lat, lng);
};

const submit = () => {
    form.post(route('settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Optional: Show toast or feedback
        },
    });
};

const handleImageUpload = (event, key) => {
    const file = event.target.files[0];
    if (file) {
        // Find setting in form and update file property
        const index = form.settings.findIndex(s => s.key === key);
        if (index !== -1) {
            form.settings[index].file = file;
            
            // Preview logic could be added here if needed, 
            // but we rely on server response to show new image after save for now
        }
    }
};

</script>

<template>
    <Head title="Pengaturan Web" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl leading-tight text-slate-900 dark:text-white">Pengaturan Web</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-slate-900 dark:text-slate-100">
                        
                        <TabGroup as="div" v-model="selectedTabIndex">
                            <TabList class="flex space-x-1 rounded-xl bg-slate-100 dark:bg-slate-900 p-1 mb-6">
                                <Tab
                                    v-for="(label, key) in groups"
                                    :key="key"
                                    as="template"
                                    v-slot="{ selected }"
                                >
                                    <button
                                        :class="[
                                            'w-full rounded-lg py-2.5 text-sm font-medium leading-5',
                                            'ring-white ring-opacity-60 ring-offset-2 ring-offset-emerald-400 focus:outline-none focus:ring-2',
                                            selected
                                                ? 'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-400 shadow'
                                                : 'text-slate-600 dark:text-slate-400 hover:bg-white/[0.12] hover:text-emerald-800 dark:hover:text-emerald-200',
                                        ]"
                                    >
                                        {{ label }}
                                    </button>
                                </Tab>
                            </TabList>

                            <TabPanels>
                                <TabPanel
                                    v-for="(label, groupKey) in groups"
                                    :key="groupKey"
                                    class="rounded-xl bg-white dark:bg-slate-800 focus:outline-none"
                                >
                                    <form @submit.prevent="submit" class="space-y-6">
                                        
                                        <!-- Location Map (Only for location group) -->
                                        <div v-if="groupKey === 'location'" class="mb-6">
                                            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mb-4">
                                                <p class="text-sm text-emerald-800 mb-3">
                                                    <strong>Cara Menggunakan:</strong> Klik tombol "Tampilkan Peta" di bawah, lalu klik pada peta untuk memilih lokasi masjid Anda. Latitude dan Longitude akan otomatis terisi.
                                                </p>
                                                <button
                                                    type="button"
                                                    @click="initializeMap"
                                                    class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors"
                                                >
                                                    <MapPinIcon class="w-5 h-5" />
                                                    {{ mapInitialized ? 'Refresh Peta' : 'Tampilkan Peta' }}
                                                </button>
                                            </div>
                                            <div id="location-map" class="h-96 rounded-lg border-2 border-slate-200 shadow-lg bg-slate-100"></div>
                                        </div>
                                        
                                        <div v-if="settings[groupKey]" class="space-y-6">
                                            <div v-for="setting in settings[groupKey]" :key="setting.id">
                                                <label :for="setting.key" class="block text-sm font-medium leading-6 text-slate-900 dark:text-white">
                                                    {{ setting.label || setting.key }}
                                                </label>
                                                
                                                <div class="mt-2 text-sm text-slate-500 mb-1" v-if="setting.group === 'hero' && setting.key === 'hero_bg_image'">
                                                    Replaces the main background image on homepage.
                                                </div>

                                                <!-- Text Input -->
                                                <div v-if="setting.type === 'text'">
                                                    <input
                                                        v-model="form.settings.find(s => s.key === setting.key).value"
                                                        :id="setting.key"
                                                        type="text"
                                                        :readonly="groupKey === 'location' && (setting.key === 'location_latitude' || setting.key === 'location_longitude')"
                                                        class="block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white"
                                                    />
                                                </div>

                                                <!-- Text Area -->
                                                <div v-else-if="setting.type === 'textarea'">
                                                    <textarea
                                                        v-model="form.settings.find(s => s.key === setting.key).value"
                                                        :id="setting.key"
                                                        rows="3"
                                                        class="block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-white shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white"
                                                    />
                                                </div>

                                                <!-- Image Upload -->
                                                <div v-else-if="setting.type === 'image'">
                                                    <div class="flex items-center gap-x-3">
                                                        <div v-if="setting.value" class="h-16 w-32 relative rounded-lg overflow-hidden border border-slate-200 bg-slate-50">
                                                             <img :src="setting.value" class="object-cover w-full h-full" alt="Current Image" />
                                                        </div>
                                                        <PhotoIcon v-else class="h-12 w-12 text-slate-300" aria-hidden="true" />
                                                        
                                                        <input
                                                            type="file"
                                                            @change="(e) => handleImageUpload(e, setting.key)"
                                                            accept="image/*"
                                                            class="block w-full text-sm text-slate-500
                                                                file:mr-4 file:py-2 file:px-4
                                                                file:rounded-full file:border-0
                                                                file:text-sm file:font-semibold
                                                                file:bg-emerald-50 file:text-emerald-700
                                                                hover:file:bg-emerald-100
                                                            "
                                                        />
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                        <div v-else class="text-slate-500 italic">
                                            Tidak ada pengaturan di bagian ini.
                                        </div>

                                        <div class="flex justify-end border-t border-slate-100 pt-6">
                                            <button
                                                type="submit"
                                                :disabled="form.processing"
                                                class="rounded-md bg-emerald-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 flex items-center gap-2"
                                            >
                                                <div v-if="form.processing" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></div>
                                                Simpan Perubahan
                                            </button>
                                        </div>

                                        <div v-if="form.recentlySuccessful" class="text-center">
                                            <p class="text-sm font-medium text-emerald-600 flex items-center justify-center gap-1">
                                                <CheckCircleIcon class="h-5 w-5" />
                                                Berhasil disimpan!
                                            </p>
                                        </div>

                                    </form>
                                </TabPanel>
                            </TabPanels>
                        </TabGroup>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
