let map;
let marker;
const defaultLocation = [-5.135399, 119.423790]; // Makassar

function initMap(containerId, latitude = null, longitude = null) {
    // Initialize map
    map = L.map(containerId).setView(
        latitude && longitude ? [latitude, longitude] : defaultLocation,
        13
    );

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Add marker if coordinates are provided
    if (latitude && longitude) {
        marker = L.marker([latitude, longitude], {
            draggable: true
        }).addTo(map);
    }

    // Handle map click
    map.on('click', function(e) {
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

    // Update or create marker
    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        marker = L.marker([lat, lng], {
            draggable: true
        }).addTo(map);
    }

    // Center map on marker
    map.setView([lat, lng]);

    // Update marker drag event
    marker.on('dragend', function(e) {
        const position = e.target.getLatLng();
        updateMarkerPosition(position.lat, position.lng);
    });

    // Get address details using Nominatim
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`);
        const data = await response.json();
        
        if (data.address) {
            // Update address fields
            const address = data.address;
            
            // Update address line
            let addressLine = '';
            if (address.road) addressLine += `Jl. ${address.road}`;
            if (address.house_number) addressLine += ` No. ${address.house_number}`;
            if (address.suburb) addressLine += `, ${address.suburb}`;
            if (addressLine) {
                document.getElementById('address_line').value = addressLine;
            }

            // Update postal code if available
            if (address.postcode) {
                document.getElementById('postal_code').value = address.postcode;
            }

            // Try to match and select province
            if (address.state) {
                const provinceSelect = document.getElementById('province_id');
                for (let i = 0; i < provinceSelect.options.length; i++) {
                    if (provinceSelect.options[i].text.toLowerCase().includes(address.state.toLowerCase())) {
                        provinceSelect.value = provinceSelect.options[i].value;
                        // Trigger change event to load cities
                        provinceSelect.dispatchEvent(new Event('change'));
                        
                        // Wait for cities to load then try to select the city
                        setTimeout(async () => {
                            const citySelect = document.getElementById('city_id');
                            if (address.city || address.town || address.municipality) {
                                const cityName = address.city || address.town || address.municipality;
                                for (let j = 0; j < citySelect.options.length; j++) {
                                    if (citySelect.options[j].text.toLowerCase().includes(cityName.toLowerCase())) {
                                        citySelect.value = citySelect.options[j].value;
                                        // Trigger change event to load districts
                                        citySelect.dispatchEvent(new Event('change'));
                                        
                                        // Wait for districts to load then try to select the district
                                        setTimeout(() => {
                                            const districtSelect = document.getElementById('district_id');
                                            if (address.suburb || address.district) {
                                                const districtName = address.suburb || address.district;
                                                for (let k = 0; k < districtSelect.options.length; k++) {
                                                    if (districtSelect.options[k].text.toLowerCase().includes(districtName.toLowerCase())) {
                                                        districtSelect.value = districtSelect.options[k].value;
                                                        break;
                                                    }
                                                }
                                            }
                                        }, 1000);
                                        break;
                                    }
                                }
                            }
                        }, 1000);
                        break;
                    }
                }
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
