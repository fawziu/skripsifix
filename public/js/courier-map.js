let map;
let markers = [];
let currentLocationMarker;
let watchId;

function initCourierMap(containerId, addresses) {
    // Initialize map centered on first address or default location
    const defaultLocation = [-6.200000, 106.816666]; // Jakarta
    const firstAddress = addresses.length > 0 ? addresses[0] : null;
    const initialLocation = firstAddress 
        ? [firstAddress.latitude, firstAddress.longitude] 
        : defaultLocation;

    // Create map
    map = L.map(containerId).setView(initialLocation, 13);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Add markers for all addresses
    addresses.forEach(address => {
        addAddressMarker(address);
    });

    // Fit map bounds to show all markers
    if (markers.length > 0) {
        const group = new L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    }

    // Add current location control
    addCurrentLocationControl();
}

function addAddressMarker(address) {
    if (!address.latitude || !address.longitude) return;

    const marker = L.marker([address.latitude, address.longitude])
        .addTo(map);

    // Create popup content
    const popupContent = `
        <div class="p-2">
            <h3 class="font-bold">${address.label}</h3>
            <p class="text-sm text-gray-600">${address.recipient_name}</p>
            <p class="text-sm text-gray-600">${address.phone}</p>
            <p class="text-sm text-gray-600">${address.address_line}</p>
            <div class="mt-2">
                <a href="https://www.google.com/maps/dir/?api=1&destination=${address.latitude},${address.longitude}"
                   target="_blank"
                   class="text-blue-600 hover:text-blue-800 text-sm">
                    <i class="fas fa-directions mr-1"></i>Petunjuk Arah
                </a>
            </div>
        </div>
    `;

    marker.bindPopup(popupContent);
    markers.push(marker);
}

function addCurrentLocationControl() {
    // Create custom control
    const locationControl = L.Control.extend({
        options: {
            position: 'topleft'
        },

        onAdd: function(map) {
            const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
            const button = L.DomUtil.create('a', 'leaflet-control-button', container);
            button.innerHTML = '<i class="fas fa-location-arrow"></i>';
            button.href = '#';
            button.title = 'Lokasi Saya';
            
            L.DomEvent.on(button, 'click', function(e) {
                L.DomEvent.stopPropagation(e);
                L.DomEvent.preventDefault(e);
                toggleLocationTracking();
            });
            
            return container;
        }
    });

    // Add control to map
    map.addControl(new locationControl());
}

function toggleLocationTracking() {
    if (!watchId) {
        // Start tracking
        if (!navigator.geolocation) {
            alert('Geolocation tidak didukung oleh browser Anda');
            return;
        }

        watchId = navigator.geolocation.watchPosition(
            updateCurrentLocation,
            handleLocationError,
            {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            }
        );
    } else {
        // Stop tracking
        navigator.geolocation.clearWatch(watchId);
        watchId = null;
        if (currentLocationMarker) {
            map.removeLayer(currentLocationMarker);
            currentLocationMarker = null;
        }
    }
}

function updateCurrentLocation(position) {
    const lat = position.coords.latitude;
    const lng = position.coords.longitude;
    const accuracy = position.coords.accuracy;

    // Create or update marker
    if (!currentLocationMarker) {
        currentLocationMarker = L.marker([lat, lng], {
            icon: L.divIcon({
                className: 'current-location-marker',
                html: '<div class="ping"></div>'
            })
        }).addTo(map);

        // Add accuracy circle
        currentLocationMarker.accuracyCircle = L.circle([lat, lng], {
            radius: accuracy,
            weight: 1,
            color: '#4B5563',
            fillColor: '#4B5563',
            fillOpacity: 0.1
        }).addTo(map);
    } else {
        currentLocationMarker.setLatLng([lat, lng]);
        currentLocationMarker.accuracyCircle.setLatLng([lat, lng]);
        currentLocationMarker.accuracyCircle.setRadius(accuracy);
    }

    // Center map on current location
    map.setView([lat, lng]);
}

function handleLocationError(error) {
    let message = 'Error: ';
    switch(error.code) {
        case error.PERMISSION_DENIED:
            message += 'Izin akses lokasi ditolak.';
            break;
        case error.POSITION_UNAVAILABLE:
            message += 'Informasi lokasi tidak tersedia.';
            break;
        case error.TIMEOUT:
            message += 'Waktu permintaan lokasi habis.';
            break;
        default:
            message += 'Terjadi kesalahan yang tidak diketahui.';
    }
    alert(message);
    
    if (watchId) {
        navigator.geolocation.clearWatch(watchId);
        watchId = null;
    }
}
