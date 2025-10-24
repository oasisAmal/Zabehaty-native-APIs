<?php

namespace Modules\Users\App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Users\App\Models\UserAddress;
use Modules\Users\App\Http\Requests\StoreAddressRequest;
use Modules\Users\App\Http\Requests\UpdateAddressRequest;
use Modules\Users\App\Http\Resources\UserAddressResource;

class AddressController extends Controller
{
    public function store(StoreAddressRequest $request)
    {
        $data = $request->validated();

        $lat = (float) $data['lat'];
        $lng = (float) $data['lng'];

        // Spatial contains check against supported UAE regions
        $region = Region::pointInsideAny($lat, $lng);
        if (!$region) {
            return responseErrorMessage(__('users::messages.location_not_supported'), 422);
        }

        $data['user_id'] = $request->user()->id;
        $data['region_id'] = $region->id;
        $data['emirate_id'] = $region->emirate_id;
        $data['branch_id'] = $region->branch_id;
        $data['is_default'] = UserAddress::where('user_id', $data['user_id'])->count() === 0 ? 1 : 0;
        $data['is_active'] = 1;

        if (!$request->has('name')) {
            $data['name'] = $request->user()->fullname;
        }

        if ($request->has('mobile') && $request->mobile == null) {
            unset($data['mobile']);
        }

        $address = UserAddress::create($data);

        return responseSuccessData(UserAddressResource::make($address));
    }

    public function update(UpdateAddressRequest $request, $id)
    {
        $address = UserAddress::where('user_id', $request->user()->id)->find($id);
        if (!$address) {
            return responseErrorMessage(__('users::messages.address_not_found'));
        }

        $data = $request->validated();

        $lat = (float) $data['lat'];
        $lng = (float) $data['lng'];


        // Spatial contains check against supported UAE regions
        $region = Region::pointInsideAny($lat, $lng);
        if (!$region) {
            return responseErrorMessage(__('users::messages.location_not_supported'), 422);
        }

        $data['region_id'] = $region->id;
        $data['emirate_id'] = $region->emirate_id;
        $data['branch_id'] = $region->branch_id;

        if (!$request->has('name')) {
            $data['name'] = $request->user()->fullname;
        }

        if ($request->has('mobile') && $request->mobile == null) {
            unset($data['mobile']);
        }

        $address->update($data);

        return responseSuccessData(UserAddressResource::make($address->fresh()));
    }

    public function destroy(Request $request, $id)
    {
        $address = UserAddress::where('user_id', $request->user()->id)->find($id);
        if (!$address) {
            return responseErrorMessage(__('users::messages.address_not_found'));
        }

        $address->delete();

        return responseSuccessMessage(__('users::messages.address_deleted'));
    }

    public function index(Request $request)
    {
        $address = UserAddress::where('user_id', $request->user()->id)->get();
        return responseSuccessData(UserAddressResource::collection($address));
    }
}
