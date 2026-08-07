<?php

namespace App\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InteractionEventBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxBatchSize = (int) config('experiment.max_event_batch_size', 100);

        return [
            'events' => ['required', 'array', 'min:1', "max:$maxBatchSize"],
            'events.*.event_uuid' => ['required', 'uuid', 'distinct'],
            'events.*.sequence_number' => ['required', 'integer', 'min:1', 'distinct'],
            'events.*.task_key' => [
                'nullable',
                'string',
                Rule::in(array_keys(config('experiment.tasks', []))),
            ],
            'events.*.option_id' => ['nullable', 'string', 'max:100'],
            'events.*.component_id' => ['nullable', 'string', 'max:150'],
            'events.*.event_type' => [
                'required',
                'string',
                Rule::in(config('experiment.event_types', [])),
            ],
            'events.*.client_timestamp' => ['nullable', 'date'],
            'events.*.elapsed_task_ms' => ['nullable', 'integer', 'min:0', 'max:86400000'],
            'events.*.elapsed_experiment_ms' => ['nullable', 'integer', 'min:0', 'max:604800000'],
            'events.*.delta_previous_event_ms' => ['nullable', 'integer', 'min:0', 'max:86400000'],
            'events.*.delta_previous_click_ms' => ['nullable', 'integer', 'min:0', 'max:86400000'],
            'events.*.previous_component_id' => ['nullable', 'string', 'max:150'],
            'events.*.viewport_width' => ['nullable', 'integer', 'min:1', 'max:20000'],
            'events.*.viewport_height' => ['nullable', 'integer', 'min:1', 'max:20000'],
            'events.*.input_method' => [
                'nullable',
                'string',
                Rule::in(config('experiment.input_methods', [])),
            ],
            'events.*.payload' => ['nullable', 'array'],
        ];
    }
}
