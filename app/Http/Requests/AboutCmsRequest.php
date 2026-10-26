<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AboutCmsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hero' => ['required', 'array'],
            'hero.eyebrow' => ['required', 'string', 'max:255'],
            'hero.title' => ['required', 'string', 'max:255'],
            'hero.titleHighlight' => ['required', 'string', 'max:255'],
            'hero.lead' => ['required', 'string', 'max:1000'],
            'hero.primaryCta.label' => ['required', 'string'],
            'hero.primaryCta.href' => ['required', 'string'],
            'hero.secondaryCta.label' => ['required', 'string'],
            'hero.secondaryCta.href' => ['required', 'string'],

            'stats' => ['required', 'array'],
            'stats.*.value' => ['required', 'string', 'max:50'],
            'stats.*.label' => ['required', 'string', 'max:100'],

            'banner_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            'story' => ['required', 'array'],
            'story.heading' => ['required', 'string', 'max:255'],
            'story.body' => ['required', 'array'],
            'story.body.*' => ['required', 'string'],
            'story_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            'mission_vision' => ['required', 'array'],
            'mission_vision.mission.title' => ['required', 'string'],
            'mission_vision.mission.text' => ['required', 'string'],
            'mission_vision.vision.title' => ['required', 'string'],
            'mission_vision.vision.text' => ['required', 'string'],

            'cta' => ['required', 'array'],
            'cta.title' => ['required', 'string'],
            'cta.text' => ['required', 'string'],
            'cta.cta.label' => ['required', 'string'],
            'cta.cta.href' => ['required', 'string'],
        ];
    }
}
