<?php

namespace Bnb\Laravel\Attachments;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait HasAttachment
{

    /**
     * Get the attachments relation morphed to the current model class
     *
     * @return MorphMany
     */
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'model');
    }


    /**
     * Find an attachment by key
     *
     * @param string $key
     *
     * @return Attachment|null
     */
    public function attachment($key)
    {
        return $this->attachments()->where('key', $key)->first();
    }


    /**
     * @param UploadedFile|string $fileOrPath
     * @param array               $options Set attachment options : title, description, key, disk
     *
     * @return Attachment|null
     */
    public function attach($fileOrPath, $options = [])
    {
        if ( ! is_array($options)) {
            throw new \Exception('Attachment options must be an array');
        }

        $options = array_only($options, ['title', 'description', 'key', 'disk']);

        if ( ! empty($options['key']) && $attachment = $this->attachment($options['key'])) {
            $attachment->delete();
        }

        /** @var Attachment $attachment */
        $attachment = new Attachment($options);

        if ($fileOrPath instanceof UploadedFile) {
            $attachment->fromPost($fileOrPath);
        } else {
            $attachment->fromFile($fileOrPath);
        }

        if ($attachment = $this->attachments()->save($attachment)) {
            return $attachment;
        }

        return null;
    }
}