<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Services\LocalGeographicalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    private LocalGeographicalService $localGeographicalService;

    public function __construct(LocalGeographicalService $localGeographicalService)
    {
        $this->localGeographicalService = $localGeographicalService;
    }

    /**
     * Display a listing of user addresses
     */
    public function index()
    {
        $user = Auth::user();
        $addresses = $user->addresses()->with(['province', 'city', 'district'])->get();

        return view('addresses.index', compact('addresses'));
    }

    /**
     * Show the form for creating a new address
     */
    public function create()
    {
        // Restrict to local mapping provinces (Sulsel and Papua Barat)
        $provinces = $this->localGeographicalService->getProvinces();
        return view('addresses.create', compact('provinces'));
    }

    /**
     * Store a newly created address
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:home,office,warehouse,other',
            'label' => 'required|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'province_id' => 'required|integer',
            'city_id' => 'required|integer',
            'district_id' => 'nullable|integer',
            'postal_code' => 'nullable|string|max:10',
            'address_line' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'is_primary' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = Auth::user();

            // If this is primary address, unset other primary addresses
            if ($request->boolean('is_primary')) {
                $user->addresses()->where('is_primary', true)->update(['is_primary' => false]);
            }

            $address = $user->addresses()->create($request->all());

            return redirect()->route('addresses.index')
                ->with('success', 'Alamat berhasil ditambahkan!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['general' => 'Gagal menambahkan alamat. Silakan coba lagi.'])
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified address
     */
    public function edit(Address $address)
    {
        // Check if user owns this address
        if ($address->user_id !== Auth::id()) {
            return redirect()->route('addresses.index')
                ->with('error', 'Akses ditolak.');
        }

        // Use local mapping data to ensure consistency with frontend mapping
        $provinces = $this->localGeographicalService->getProvinces();
        $cities = $this->localGeographicalService->getCities((int)$address->province_id);
        $districts = $this->localGeographicalService->getDistricts((int)$address->city_id);

        return view('addresses.edit', compact('address', 'provinces', 'cities', 'districts'));
    }

    /**
     * Update the specified address
     */
    public function update(Request $request, Address $address)
    {
        // Check if user owns this address
        if ($address->user_id !== Auth::id()) {
            return redirect()->route('addresses.index')
                ->with('error', 'Akses ditolak.');
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:home,office,warehouse,other',
            'label' => 'required|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'province_id' => 'required|integer',
            'city_id' => 'required|integer',
            'district_id' => 'nullable|integer',
            'postal_code' => 'nullable|string|max:10',
            'address_line' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'is_primary' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = Auth::user();

            // If this is primary address, unset other primary addresses
            if ($request->boolean('is_primary')) {
                $user->addresses()->where('id', '!=', $address->id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }

            $address->update($request->all());

            return redirect()->route('addresses.index')
                ->with('success', 'Alamat berhasil diperbarui!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['general' => 'Gagal memperbarui alamat. Silakan coba lagi.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified address
     */
    public function destroy(Address $address)
    {
        // Check if user owns this address
        if ($address->user_id !== Auth::id()) {
            return redirect()->route('addresses.index')
                ->with('error', 'Akses ditolak.');
        }

        try {
            $address->delete();

            return redirect()->route('addresses.index')
                ->with('success', 'Alamat berhasil dihapus!');

        } catch (\Exception $e) {
            return redirect()->route('addresses.index')
                ->with('error', 'Gagal menghapus alamat. Silakan coba lagi.');
        }
    }

    /**
     * Set address as primary
     */
    public function setPrimary(Address $address)
    {
        // Check if user owns this address
        if ($address->user_id !== Auth::id()) {
            return redirect()->route('addresses.index')
                ->with('error', 'Akses ditolak.');
        }

        try {
            $user = Auth::user();

            // Unset other primary addresses
            $user->addresses()->where('id', '!=', $address->id)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);

            // Set this address as primary
            $address->update(['is_primary' => true]);

            return redirect()->route('addresses.index')
                ->with('success', 'Alamat utama berhasil diubah!');

        } catch (\Exception $e) {
            return redirect()->route('addresses.index')
                ->with('error', 'Gagal mengubah alamat utama. Silakan coba lagi.');
        }
    }

    /**
     * Get cities by province (AJAX)
     */
    public function getCities(Request $request)
    {
        $provinceId = (int) $request->get('province_id');
        $cities = $this->localGeographicalService->getCities($provinceId);

        return response()->json(['data' => $cities]);
    }

    /**
     * Get districts by city (AJAX)
     */
    public function getDistricts(Request $request)
    {
        $cityId = (int) $request->get('city_id');
        $districts = $this->localGeographicalService->getDistricts($cityId);

        return response()->json(['data' => $districts]);
    }

    /**
     * Get all provinces (API)
     */
    public function getProvinces()
    {
        $provinces = Province::active()->get();

        return response()->json([
            'success' => true,
            'data' => $provinces
        ]);
    }

    /**
     * Get cities by province (API for RajaOngkir)
     */
    public function getCitiesApi(Request $request)
    {
        $request->validate([
            'province_id' => 'required|integer'
        ]);

        $cities = $this->localGeographicalService->getCities($request->province_id);

        return response()->json([
            'success' => true,
            'data' => $cities
        ]);
    }

    /**
     * Get districts by city (API)
     */
    public function getDistrictsApi(Request $request)
    {
        $request->validate([
            'city_id' => 'required|integer'
        ]);

        $districts = $this->localGeographicalService->getDistricts($request->city_id);

        return response()->json([
            'success' => true,
            'data' => $districts
        ]);
    }

    /**
     * Display map view of addresses for courier
     */
    public function map()
    {
        // Only allow courier access
        if (!Auth::user()->isCourier()) {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $addresses = Address::with(['province', 'city', 'district'])
            ->where('is_active', true)
            ->get();

        return view('addresses.map', compact('addresses'));
    }
}
