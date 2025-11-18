<?php

namespace Modules\Users\App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Users\App\Services\UserAddressService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Users\App\Http\Requests\StoreAddressRequest;
use Modules\Users\App\Http\Requests\UpdateAddressRequest;
use Modules\Users\App\Http\Resources\UserAddressResource;
use Modules\Users\App\Http\Requests\SetDefaultAddressRequest;

class AddressController extends Controller
{
    protected UserAddressService $userAddressService;

    public function __construct(UserAddressService $userAddressService)
    {
        $this->userAddressService = $userAddressService;
    }

    public function store(StoreAddressRequest $request)
    {
        try {
            $address = $this->userAddressService->create($request->validated());
            return responseSuccessData(UserAddressResource::make($address));
        } catch (HttpResponseException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return responseErrorMessage(
                __('users::messages.failed_to_create_address'),
                500
            );
        }
    }

    public function update(UpdateAddressRequest $request, $id)
    {
        try {
            $address = $this->userAddressService->update($request->validated(), $id);
            return responseSuccessData(UserAddressResource::make($address));
        } catch (HttpResponseException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return responseErrorMessage(
                __('users::messages.failed_to_update_address'),
                500
            );
        }
        return responseSuccessData(UserAddressResource::make($address));
    }

    public function destroy(Request $request, $id)
    {
        try {
            $this->userAddressService->delete($request->validated(), $id);
            return responseSuccessMessage(__('users::messages.address_deleted'));
        } catch (HttpResponseException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return responseErrorMessage(
                __('users::messages.failed_to_delete_address'),
                500
            );
        }
        return responseSuccessMessage(__('users::messages.address_deleted'));
    }

    public function index(Request $request)
    {
        try {
            $address = $this->userAddressService->paginate($request->all());
            return responsePaginate(UserAddressResource::collection($address));
        } catch (\Exception $e) {
            return responseErrorMessage(
                __('users::messages.failed_to_retrieve_addresses'),
                500
            );
        }
    }

    public function setDefault(SetDefaultAddressRequest $request)
    {
        try {
            $address = $this->userAddressService->setDefault($request->validated());
            return responseSuccessData(UserAddressResource::make($address));
        } catch (HttpResponseException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return responseErrorMessage(
                __('users::messages.failed_to_set_default_address'),
                500
            );
        }
    }
}
