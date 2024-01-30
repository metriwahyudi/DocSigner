<?php

namespace App\Services\Bitrix24\Contracts;

use App\Services\Bitrix24\Bitrix24;

interface Bitrix24Interface
{
    public function selectSpa(int $spaID) : Bitrix24;
    public function selectItem(int $itemID) : Bitrix24;
    public function updateSpaImageFile(string $fieldId,  string $filename, string $base64_file);
    public function makeSignature(array $data);
    public function getSPA(): array;
    public function notifyUser(int $user_id, string $message);
    public function getForm(int $id): array;
    public function getFormList(): array;
}
