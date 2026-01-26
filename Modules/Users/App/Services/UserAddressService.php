<?php

namespace Modules\Users\App\Services;

use App\Enums\Pagination;
use App\Models\Region;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Users\App\Models\UserAddress;
use Modules\Users\App\Services\AddressStateEvaluationService;

class UserAddressService
{
    public function create(array $data): UserAddress
    {
        $data = $this->buildAddressPayload($data, true);

        if (auth('api')->user()->isGuest()) {
            return UserAddress::updateOrCreate(['user_id' => $data['user_id']], $data);
        }

        if (!isset($data['mobile'])) {
            $data['mobile'] = auth('api')->user()->mobile;
            $data['country_code'] = auth('api')->user()->country_symbol;
        }

        $address = UserAddress::create($data);

        UserAddress::where('user_id', $data['user_id'])->where('id', '!=', $address->id)->update(['is_default' => false]);

        // Update address state cache after creation
        /** @var \Modules\Users\App\Models\User|null $user */
        $user = auth('api')->user();
        if ($user && !$user->isGuest()) {
            /** @var \Modules\Users\App\Models\User $freshUser */
            $freshUser = $user->fresh();
            app(AddressStateEvaluationService::class)->updateAddressStateCache($freshUser);
        }

        return $address;
    }

    public function update(array $data, int $addressId): UserAddress
    {
        $address = $this->findUserAddressOrFail(auth('api')->user()->id, $addressId);

        $data = $this->buildAddressPayload($data, false);

        $address->update($data);

        $updatedAddress = $address->fresh();

        // Update address state cache after update
        /** @var \Modules\Users\App\Models\User|null $user */
        $user = auth('api')->user();
        if ($user && !$user->isGuest()) {
            /** @var \Modules\Users\App\Models\User $freshUser */
            $freshUser = $user->fresh();
            app(AddressStateEvaluationService::class)->updateAddressStateCache($freshUser);
        }

        return $updatedAddress;
    }

    public function delete(int $addressId): void
    {
        $user = auth('api')->user();
        $defaultAddress = $user->defaultAddress;

        if ($defaultAddress?->id == $addressId) {
            /** @var \Symfony\Component\HttpFoundation\Response $response */
            $response = responseErrorMessage(__('users::messages.default_address_cannot_be_deleted'), 422);
            throw new HttpResponseException($response);
        }

        $address = $this->findUserAddressOrFail($user->id, $addressId);

        $address->delete();

        // Update address state cache after deletion
        if (!$user->isGuest()) {
            /** @var \Modules\Users\App\Models\User $freshUser */
            $freshUser = $user->fresh();
            app(AddressStateEvaluationService::class)->updateAddressStateCache($freshUser);
        }
    }

    public function paginate(array $data): LengthAwarePaginator
    {
        return UserAddress::where('user_id', auth('api')->user()->id)
            ->active()
            ->orderByDesc('is_default')
            ->paginate(Pagination::PER_PAGE);
    }

    public function setDefault(array $data): UserAddress
    {
        $address = $this->findUserAddressOrFail(auth('api')->user()->id, $data['address_id']);

        $user = auth('api')->user();
        
        UserAddress::where('user_id', $user->id)->default()->update(['is_default' => false]);

        $address->update(['is_default' => true]);

        $updatedAddress = $address->fresh();

        // Update address state cache after setting default
        if (!$user->isGuest()) {
            /** @var \Modules\Users\App\Models\User $freshUser */
            $freshUser = $user->fresh();
            app(AddressStateEvaluationService::class)->updateAddressStateCache($freshUser);
        }

        return $updatedAddress;
    }

    /**
     * @param array $data
     * @param bool $isCreate
     * @return array
     * @throws HttpResponseException
     */
    protected function buildAddressPayload(array $data, bool $isCreate): array
    {
        $lat = (float) $data['lat'];
        $lng = (float) $data['lng'];

        $region = $this->getRegionOrFail($lat, $lng);

        $data['region_id'] = $region->id;
        $data['emirate_id'] = $region->emirate_id;
        $data['branch_id'] = $region->branch_id;

        if ($isCreate) {
            $data['user_id'] = auth('api')->user()->id;
            // $data['is_default'] = UserAddress::where('user_id', $data['user_id'])->count() >= 1 ? 0 : 1;
            $data['is_active'] = 1;
        }

        if (!isset($data['name'])) {
            $data['name'] = auth('api')->user()->fullname ?? '';
        }

        if ($data['mobile'] == null) {
            unset($data['mobile']);
            unset($data['country_code']);
            unset($data['mobile_country_code']);
            unset($data['validate_mobile']);
        } else {
            $data['mobile'] = $data['validate_mobile'];
        }

        return $data;
    }

    /**
     * @throws HttpResponseException
     */
    protected function getRegionOrFail(float $lat, float $lng): Region
    {
        $region = Region::pointInsideAny($lat, $lng);

        if (!$region) {
            /** @var \Symfony\Component\HttpFoundation\Response $response */
            $response = responseErrorMessage(__('users::messages.location_not_supported'), 422);
            throw new HttpResponseException($response);
        }

        return $region;
    }

    /**
     * @throws HttpResponseException
     */
    public function findUserAddressOrFail(int $userId, int $addressId): UserAddress
    {
        $address = UserAddress::where('user_id', $userId)->find($addressId);

        if (!$address) {
            /** @var \Symfony\Component\HttpFoundation\Response $response */
            $response = responseErrorMessage(__('users::messages.address_not_found'));
            throw new HttpResponseException($response);
        }

        return $address;
    }
}
