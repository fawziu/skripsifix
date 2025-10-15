let map;
let marker;
let allowPointPick = false;
const defaultLocation = [-5.135399, 119.423790]; // Makassar

function initMap(containerId, latitude = null, longitude = null) {
    // Initialize map
    // If coords not provided, try hidden inputs
    if ((latitude === null || longitude === null)) {
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const latVal = latInput && latInput.value ? parseFloat(latInput.value) : null;
        const lngVal = lngInput && lngInput.value ? parseFloat(lngInput.value) : null;
        if (!isNaN(latVal) && !isNaN(lngVal)) {
            latitude = latVal;
            longitude = lngVal;
        }
    }

    map = L.map(containerId).setView(
        (latitude !== null && longitude !== null) ? [latitude, longitude] : defaultLocation,
        (latitude !== null && longitude !== null) ? 15 : 13
    );

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Prevent page scroll from zooming the map
    map.scrollWheelZoom.disable();

    // Add marker if coordinates are provided (non-draggable)
    if (latitude !== null && longitude !== null) {
        marker = L.marker([latitude, longitude], {
            draggable: false
        }).addTo(map);
    }

    // If province or city selection exists on page, focus map accordingly
    const provinceSelect = document.getElementById('province_id');
    const citySelect = document.getElementById('city_id');
    if (provinceSelect) {
        provinceSelect.addEventListener('change', function() {
            // Rough centroids for supported provinces
            const provinceCenters = {
                648: [-5.135399, 119.423790], // Sulawesi Selatan (Makassar)
                546: [-0.861453, 134.062042], // Papua Barat (Manokwari vicinity)
            };
            const pid = parseInt(this.value, 10);
            if (provinceCenters[pid]) {
                map.setView(provinceCenters[pid], 8);
            }
            // Disable point picking and clear coordinates until city/district chosen
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            if (latInput) latInput.value = '';
            if (lngInput) lngInput.value = '';
            allowPointPick = false;
        });
    }
    if (citySelect) {
        citySelect.addEventListener('change', async function() {
            // Try to geocode the selected city to set map view and drop marker
            const selectedOption = this.options[this.selectedIndex];
            const cityName = selectedOption ? selectedOption.text : '';
            const provinceName = (provinceSelect && provinceSelect.options[provinceSelect.selectedIndex]) ? provinceSelect.options[provinceSelect.selectedIndex].text : '';
            if (!cityName) return;
            try {
                const query = `${cityName}, ${provinceName}, Indonesia`;
                const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`);
                const results = await res.json();
                if (Array.isArray(results) && results.length > 0) {
                    const lat = parseFloat(results[0].lat);
                    const lon = parseFloat(results[0].lon);
                    updateMarkerPosition(lat, lon);
                }
            } catch (e) {
                // Silent fail if geocoding fails
            }
            // Disable point picking until district selected
            allowPointPick = false;
        });
    }

    // District change: refine coordinates using district + city + province
    const districtSelect = document.getElementById('district_id');
    if (districtSelect) {
        districtSelect.addEventListener('change', async function() {
            const districtOption = this.options[this.selectedIndex];
            const districtName = districtOption ? districtOption.text : '';
            const cityOption = citySelect && citySelect.options[citySelect.selectedIndex];
            const cityName = cityOption ? cityOption.text : '';
            const provinceName = (provinceSelect && provinceSelect.options[provinceSelect.selectedIndex]) ? provinceSelect.options[provinceSelect.selectedIndex].text : '';
            if (!districtName) return;
            try {
                const query = `${districtName}, ${cityName}, ${provinceName}, Indonesia`;
                const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`);
                const results = await res.json();
                if (Array.isArray(results) && results.length > 0) {
                    const lat = parseFloat(results[0].lat);
                    const lon = parseFloat(results[0].lon);
                    updateMarkerPosition(lat, lon);
                    // Prefer fitting the district bounds if available; otherwise zoom in close
                    if (results[0].boundingbox && results[0].boundingbox.length === 4) {
                        const [south, north, west, east] = results[0].boundingbox.map(parseFloat);
                        const bounds = L.latLngBounds([
                            [south, west],
                            [north, east]
                        ]);
                        map.fitBounds(bounds, { padding: [20, 20] });
                    } else {
                        map.setView([lat, lon], 17);
                    }
                }
            } catch (e) {
                // Silent fail
            }
            // Enable point picking after full selection
            allowPointPick = true;
        });
    }

    // Conditional map click: only active when province, city, and district selected
    map.on('click', function(e) {
        if (!allowPointPick) return;
        updateMarkerPosition(e.latlng.lat, e.latlng.lng);
    });
}

async function updateMarkerPosition(lat, lng, accuracy = null) {
    // Update hidden form fields
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;
    if (accuracy) {
        document.getElementById('accuracy').value = accuracy;
    }

    // Update or create marker (non-draggable)
    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        marker = L.marker([lat, lng], {
            draggable: false
        }).addTo(map);
    }

    // Center map on marker
    map.setView([lat, lng]);

    // Dragging disabled; coordinates set only via selections/geocoding

    // Get address details using Nominatim (fill address line and postal code only)
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`);
        const data = await response.json();
        
        if (data.address) {
            const address = data.address;

            let addressLine = '';
            if (address.road) addressLine += `Jl. ${address.road}`;
            if (address.house_number) addressLine += ` No. ${address.house_number}`;
            if (address.suburb) addressLine += `, ${address.suburb}`;
            if (addressLine) {
                const addressInput = document.getElementById('address_line');
                if (addressInput && !addressInput.value) {
                    addressInput.value = addressLine;
                }
            }

            if (address.postcode) {
                const postalInput = document.getElementById('postal_code');
                if (postalInput) postalInput.value = address.postcode;
            }
        }
    } catch (error) {
        console.error('Error getting address details:', error);
    }
}

async function getCurrentLocation() {
    if (!navigator.geolocation) {
        alert('Geolocation tidak didukung oleh browser Anda');
        return;
    }

    // Show loading indicator
    const loadingIndicator = document.getElementById('locationLoading');
    loadingIndicator.classList.remove('hidden');
    loadingIndicator.classList.add('flex');

    try {
        const position = await new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject, {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            });
        });

        await updateMarkerPosition(
            position.coords.latitude,
            position.coords.longitude,
            position.coords.accuracy
        );
    } catch (error) {
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
    } finally {
        // Hide loading indicator
        loadingIndicator.classList.add('hidden');
        loadingIndicator.classList.remove('flex');
    }
}
