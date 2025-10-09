/**
 * Location Tracking JavaScript
 * Handles real-time location tracking for couriers and customers
 */

class LocationTracker {
    constructor(orderId) {
        this.orderId = orderId;
        this.map = null;
        this.courierMarker = null;
        this.customerMarker = null;
        this.isTracking = false;
        this.trackingInterval = null;
        this.locationWatchId = null;
        this.courierPolyline = null;
        this.customerPolyline = null;
        this.courierLocations = [];
        this.customerLocations = [];
        
        this.init();
    }

    init() {
        this.initMap();
        this.setupEventListeners();
        this.requestLocationPermission();
        this.loadLocations();
        
        // Auto-refresh locations every 30 seconds
        setInterval(() => this.loadLocations(), 30000);
        
        // Auto-start tracking for customers
        this.checkUserTypeAndAutoStart();
        
        // Set initial map view to courier location after loading
        setTimeout(() => this.setInitialMapView(), 1000);
    }

    initMap() {
        // Default to Jakarta coordinates
        this.map = L.map('map').setView([-6.200000, 106.816666], 13);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(this.map);

        // Create custom icons with larger size
        const courierIcon = L.divIcon({
            className: 'courier-marker',
            html: '<div style="background: #3b82f6; border: 3px solid white; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 8px rgba(0,0,0,0.4);"><i class="fas fa-shipping-fast text-white" style="font-size: 18px;"></i></div>',
            iconSize: [36, 36],
            iconAnchor: [18, 18]
        });

        const customerIcon = L.divIcon({
            className: 'customer-marker',
            html: '<div style="background: #10b981; border: 3px solid white; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 8px rgba(0,0,0,0.4);"><i class="fas fa-home text-white" style="font-size: 18px;"></i></div>',
            iconSize: [36, 36],
            iconAnchor: [18, 18]
        });

        // Initialize markers with proper coordinates
        this.courierMarker = L.marker([-6.200000, 106.816666], { icon: courierIcon });
        this.customerMarker = L.marker([-6.200000, 106.816666], { icon: customerIcon });
        
        // Add markers to map
        this.courierMarker.addTo(this.map);
        this.customerMarker.addTo(this.map);
        
        // Hide markers initially by setting them to invisible coordinates
        this.courierMarker.setLatLng([0, 0]);
        this.customerMarker.setLatLng([0, 0]);
    }

    setupEventListeners() {
        const startTrackingBtn = document.getElementById('start-tracking-btn');
        const refreshLocationsBtn = document.getElementById('refresh-locations-btn');
        const centerMapBtn = document.getElementById('center-map-btn');

        if (startTrackingBtn) {
            startTrackingBtn.addEventListener('click', () => this.startTracking());
        }

        if (refreshLocationsBtn) {
            refreshLocationsBtn.addEventListener('click', () => this.loadLocations());
        }

        if (centerMapBtn) {
            centerMapBtn.addEventListener('click', () => this.centerMapOnMyLocation());
        }
    }

    checkUserTypeAndAutoStart() {
        // Check if user is customer by looking for start tracking button
        const startTrackingBtn = document.getElementById('start-tracking-btn');
        if (!startTrackingBtn) {
            // Customer - auto start tracking
            this.autoStartTrackingForCustomer();
        }
    }

    autoStartTrackingForCustomer() {
        // Auto-start tracking for customer after a short delay
        setTimeout(() => {
            if (!this.isTracking) {
                this.startTracking();
            }
        }, 2000);
    }

    requestLocationPermission() {
        if (!navigator.geolocation) {
            this.showLocationError('Geolocation tidak didukung oleh browser ini.');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                console.log('Location permission granted');
                this.hideLocationAlert();
            },
            (error) => {
                console.error('Location permission denied:', error);
                if (error.code === 3) {
                    this.showLocationError('Timeout mendapatkan lokasi. Silakan coba lagi atau periksa koneksi GPS Anda.');
                } else {
                    this.showLocationError('Izin lokasi diperlukan untuk tracking. Silakan aktifkan izin lokasi di browser Anda.');
                }
            },
            {
                enableHighAccuracy: false, // Kurangi akurasi untuk menghindari timeout
                timeout: 30000, // Perpanjang timeout menjadi 30 detik
                maximumAge: 300000 // 5 menit cache
            }
        );
    }

    showLocationError(message) {
        const alert = document.getElementById('location-permission-alert');
        if (alert) {
            alert.querySelector('p').textContent = message;
            alert.classList.remove('hidden');
        }
    }

    hideLocationAlert() {
        const alert = document.getElementById('location-permission-alert');
        if (alert) {
            alert.classList.add('hidden');
        }
    }

    startTracking() {
        if (this.isTracking) {
            this.stopTracking();
            return;
        }

        if (!navigator.geolocation) {
            this.showLocationError('Geolocation tidak didukung oleh browser ini.');
            return;
        }

        // Request permission first
        navigator.geolocation.getCurrentPosition(
            (position) => {
                this.isTracking = true;
                this.updateTrackingButton(true);

                // Start watching position
                this.locationWatchId = navigator.geolocation.watchPosition(
                    (position) => this.updateMyLocation(position),
                    (error) => {
                        console.error('Location error:', error);
                        if (error.code === 3) {
                            console.warn('Location timeout, retrying...');
                        }
                    },
                    {
                        enableHighAccuracy: false,
                        timeout: 30000,
                        maximumAge: 60000
                    }
                );

                // Start periodic location updates
                this.trackingInterval = setInterval(() => {
                    navigator.geolocation.getCurrentPosition(
                        (position) => this.updateMyLocation(position),
                        (error) => {
                            console.error('Location error:', error);
                            if (error.code === 3) {
                                console.warn('Location timeout, retrying...');
                            }
                        },
                        {
                            enableHighAccuracy: false,
                            timeout: 30000,
                            maximumAge: 60000
                        }
                    );
                }, 15000); // Update every 15 seconds

                // Load initial locations
                this.loadLocations();
            },
            (error) => {
                if (error.code === 3) {
                    this.showLocationError('Timeout mendapatkan lokasi. Silakan coba lagi atau periksa koneksi GPS Anda.');
                } else {
                    this.showLocationError('Izin lokasi diperlukan untuk tracking. Silakan aktifkan izin lokasi di browser Anda.');
                }
            },
            {
                enableHighAccuracy: false,
                timeout: 30000,
                maximumAge: 300000
            }
        );
    }

    stopTracking() {
        this.isTracking = false;
        this.updateTrackingButton(false);

        if (this.locationWatchId) {
            navigator.geolocation.clearWatch(this.locationWatchId);
        }

        if (this.trackingInterval) {
            clearInterval(this.trackingInterval);
        }
    }

    updateTrackingButton(isTracking) {
        const btn = document.getElementById('start-tracking-btn');
        if (!btn) return;

        if (isTracking) {
            btn.innerHTML = '<i class="fas fa-stop mr-2"></i>Stop Tracking';
            btn.classList.remove('bg-green-600', 'hover:bg-green-700');
            btn.classList.add('bg-red-600', 'hover:bg-red-700');
        } else {
            btn.innerHTML = '<i class="fas fa-play mr-2"></i>Mulai Tracking';
            btn.classList.remove('bg-red-600', 'hover:bg-red-700');
            btn.classList.add('bg-green-600', 'hover:bg-green-700');
        }
    }

    updateMyLocation(position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        const accuracy = position.coords.accuracy;

        // Send location to server
        fetch(`/orders/${this.orderId}/tracking/location`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                latitude: lat,
                longitude: lng,
                accuracy: accuracy,
                speed: position.coords.speed || null,
                heading: position.coords.heading || null
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Location updated successfully');
            }
        })
        .catch(error => {
            console.error('Error updating location:', error);
        });
    }

    loadLocations() {
        return fetch(`/orders/${this.orderId}/tracking/locations`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateMapWithLocations(data.data);
                return data.data;
            }
        })
        .catch(error => {
            console.error('Error loading locations:', error);
            return null;
        });
    }

    updateMapWithLocations(data) {
        const { courier_location, customer_location, tracking_history, user_type } = data;

        // Update courier location (always show for all users)
        if (courier_location) {
            const courierLatLng = [courier_location.latitude, courier_location.longitude];
            this.courierMarker.setLatLng(courierLatLng);
            this.courierMarker.bindPopup(`
                <div class="text-center">
                    <h4 class="font-semibold text-blue-600">🚚 Kurir</h4>
                    <p class="text-sm">${courier_location.user?.name || 'Unknown'}</p>
                    <p class="text-xs text-gray-500">${new Date(courier_location.tracked_at).toLocaleString()}</p>
                </div>
            `);
            
            // Update courier location status
            const statusElement = document.getElementById('courier-location-status');
            if (statusElement) {
                statusElement.textContent = 'Aktif';
            }

            // Add to polyline
            this.courierLocations.push(courierLatLng);
            this.updateCourierPolyline();
        }

        // Update customer location (only for courier and admin)
        if (customer_location && (user_type === 'courier' || user_type === 'admin')) {
            const customerLatLng = [customer_location.latitude, customer_location.longitude];
            this.customerMarker.setLatLng(customerLatLng);
            this.customerMarker.bindPopup(`
                <div class="text-center">
                    <h4 class="font-semibold text-green-600">🏠 Customer</h4>
                    <p class="text-sm">${customer_location.user?.name || 'Unknown'}</p>
                    <p class="text-xs text-gray-500">${new Date(customer_location.tracked_at).toLocaleString()}</p>
                </div>
            `);
            
            // Update customer location status
            const statusElement = document.getElementById('customer-location-status');
            if (statusElement) {
                statusElement.textContent = 'Aktif';
            }

            // Add to polyline
            this.customerLocations.push(customerLatLng);
            this.updateCustomerPolyline();
        }

        // Update last update time
        const now = new Date();
        const lastUpdateElement = document.getElementById('last-update');
        if (lastUpdateElement) {
            lastUpdateElement.textContent = `Terakhir update: ${now.toLocaleTimeString()}`;
        }

        // Fit map to show both markers
        this.fitMapToMarkers();
    }

    updateCourierPolyline() {
        if (this.courierPolyline) {
            this.map.removeLayer(this.courierPolyline);
        }

        if (this.courierLocations.length > 1) {
            this.courierPolyline = L.polyline(this.courierLocations, {
                color: '#3b82f6',
                weight: 3,
                opacity: 0.7
            }).addTo(this.map);
        }
    }

    updateCustomerPolyline() {
        if (this.customerPolyline) {
            this.map.removeLayer(this.customerPolyline);
        }

        if (this.customerLocations.length > 1) {
            this.customerPolyline = L.polyline(this.customerLocations, {
                color: '#10b981',
                weight: 3,
                opacity: 0.7
            }).addTo(this.map);
        }
    }

    setInitialMapView() {
        // Try to center map on courier location first
        this.loadLocations().then((data) => {
            if (data && data.courier_location) {
                const courierLatLng = [data.courier_location.latitude, data.courier_location.longitude];
                this.map.setView(courierLatLng, 15);
            }
        });
    }

    fitMapToMarkers() {
        const markers = [];
        
        // Check if markers exist and are visible
        if (this.courierMarker && this.courierMarker.getLatLng && this.courierMarker.getLatLng().lat !== 0) {
            markers.push(this.courierMarker);
        }
        
        if (this.customerMarker && this.customerMarker.getLatLng && this.customerMarker.getLatLng().lat !== 0) {
            markers.push(this.customerMarker);
        }

        if (markers.length > 0) {
            const group = new L.featureGroup(markers);
            this.map.fitBounds(group.getBounds().pad(0.1));
        } else if (markers.length === 1) {
            // If only one marker, center on it with higher zoom
            this.map.setView(markers[0].getLatLng(), 15);
        }
    }

    centerMapOnMyLocation() {
        if (!navigator.geolocation) {
            this.showLocationError('Geolocation tidak didukung oleh browser ini.');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                this.map.setView([lat, lng], 15);
            },
            (error) => {
                this.showLocationError('Tidak dapat mendapatkan lokasi Anda.');
            }
        );
    }
}

// Initialize tracking when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Get order ID from the current page
    const orderId = window.location.pathname.match(/\/orders\/(\d+)\/tracking/);
    if (orderId && orderId[1]) {
        window.locationTracker = new LocationTracker(orderId[1]);
    }
});
