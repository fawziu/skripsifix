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
        $provinces = Province::active()->get();
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

        $provinces = Province::active()->get();
        $cities = City::where('province_id', $address->province_id)->active()->get();
        $districts = District::where('city_id', $address->city_id)->active()->get();

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
        $provinceId = $request->get('province_id');
        $cities = City::where('province_id', $provinceId)->active()->get();

        return response()->json($cities);
    }

    /**
     * Get districts by city (AJAX)
     */
    public function getDistricts(Request $request)
    {
        $cityId = $request->get('city_id');
        $districts = District::where('city_id', $cityId)->active()->get();

        return response()->json($districts);
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
}
