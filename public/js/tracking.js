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
    }

    initMap() {
        // Default to Jakarta coordinates
        this.map = L.map('map').setView([-6.200000, 106.816666], 13);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(this.map);

        // Create custom icons
        const courierIcon = L.divIcon({
            className: 'courier-marker',
            html: '<i class="fas fa-truck text-white text-xs" style="margin-left: 4px; margin-top: 2px;"></i>',
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });

        const customerIcon = L.divIcon({
            className: 'customer-marker',
            html: '<i class="fas fa-user text-white text-xs" style="margin-left: 4px; margin-top: 2px;"></i>',
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });

        // Initialize markers
        this.courierMarker = L.marker([0, 0], { icon: courierIcon }).addTo(this.map);
        this.customerMarker = L.marker([0, 0], { icon: customerIcon }).addTo(this.map);
        
        // Hide markers initially
        this.courierMarker.setOpacity(0);
        this.customerMarker.setOpacity(0);
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
                this.showLocationError('Izin lokasi diperlukan untuk tracking. Silakan aktifkan izin lokasi di browser Anda.');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000
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
                    (error) => console.error('Location error:', error),
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 30000
                    }
                );

                // Start periodic location updates
                this.trackingInterval = setInterval(() => {
                    navigator.geolocation.getCurrentPosition(
                        (position) => this.updateMyLocation(position),
                        (error) => console.error('Location error:', error),
                        {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 30000
                        }
                    );
                }, 10000); // Update every 10 seconds

                // Load initial locations
                this.loadLocations();
            },
            (error) => {
                this.showLocationError('Izin lokasi diperlukan untuk tracking. Silakan aktifkan izin lokasi di browser Anda.');
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
        fetch(`/orders/${this.orderId}/tracking/locations`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateMapWithLocations(data.data);
            }
        })
        .catch(error => {
            console.error('Error loading locations:', error);
        });
    }

    updateMapWithLocations(data) {
        const { courier_location, customer_location, tracking_history } = data;

        // Update courier location
        if (courier_location) {
            const courierLatLng = [courier_location.latitude, courier_location.longitude];
            this.courierMarker.setLatLng(courierLatLng);
            this.courierMarker.setOpacity(1);
            this.courierMarker.bindPopup(`
                <div class="text-center">
                    <h4 class="font-semibold text-blue-600">Kurir</h4>
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

        // Update customer location
        if (customer_location) {
            const customerLatLng = [customer_location.latitude, customer_location.longitude];
            this.customerMarker.setLatLng(customerLatLng);
            this.customerMarker.setOpacity(1);
            this.customerMarker.bindPopup(`
                <div class="text-center">
                    <h4 class="font-semibold text-green-600">Customer</h4>
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

    fitMapToMarkers() {
        const markers = [];
        
        if (this.courierMarker.getOpacity() > 0) {
            markers.push(this.courierMarker);
        }
        
        if (this.customerMarker.getOpacity() > 0) {
            markers.push(this.customerMarker);
        }

        if (markers.length > 0) {
            const group = new L.featureGroup(markers);
            this.map.fitBounds(group.getBounds().pad(0.1));
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
