<?php

namespace Modules\Users\App\Services;

use App\Enums\Pagination;
use App\Models\Region;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Users\App\Models\UserAddress;

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
            $data['country_code'] = auth('api')->user()->country_code;
        }

        return UserAddress::create($data);
    }

    public function update(array $data, int $addressId): UserAddress
    {
        $address = $this->findUserAddressOrFail(auth('api')->user()->id, $addressId);

        $data = $this->buildAddressPayload($data, false);

        $address->update($data);

        return $address->fresh();
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
    }

    public function paginate(array $data): LengthAwarePaginator
    {
        return UserAddress::where('user_id', auth('api')->user()->id)->paginate(Pagination::PER_PAGE);
    }

    public function setDefault(array $data): UserAddress
    {
        $address = $this->findUserAddressOrFail(auth('api')->user()->id, $data['address_id']);

        UserAddress::where('user_id', auth('api')->user()->id)->default()->update(['is_default' => false]);

        $address->update(['is_default' => true]);

        return $address->fresh();
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
            $data['is_default'] = UserAddress::where('user_id', $data['user_id'])->count() > 1 ? 0 : 1;
            $data['is_active'] = 1;
        }

        if (!isset($data['name'])) {
            $data['name'] = auth('api')->user()->fullname ?? '';
        }

        if (isset($data['mobile']) && $data['mobile'] == null) {
            unset($data['mobile']);
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
    protected function findUserAddressOrFail(int $userId, int $addressId): UserAddress
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
