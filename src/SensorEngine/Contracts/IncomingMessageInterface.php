<?php

declare(strict_types=1);

namespace OpenDialogAi\SensorEngine\Contracts;

interface IncomingMessageInterface
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array;
}
