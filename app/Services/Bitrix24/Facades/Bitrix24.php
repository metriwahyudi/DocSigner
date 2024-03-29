<?php

namespace App\Services\Bitrix24\Facades;

use App\Models\Document;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Bitrix24 selectSpa(int $spaID)
 * @method static Bitrix24 selectItem(int $itemID)
 * @method static void updateSpaImageFile(string $fieldId,  string $filename, string $base64_file)
 * @method static void makeSignature(array $data)
 * @method static array getSPA()
 * @method static void notifyUser(int $user_id, string $message)
 * @method static array getForm(int $id)
 * @method static array getFormList()
 * @method static Document|null loadDocument(int $template_id)
 * @method static Document|null updateDocument(Document $document)
 */

class Bitrix24 extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'bitrix24';
    }
}
