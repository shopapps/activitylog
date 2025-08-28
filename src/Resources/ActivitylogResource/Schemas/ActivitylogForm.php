<?php

namespace Rmsramos\Activitylog\Resources\ActivitylogResource\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Rmsramos\Activitylog\ActivitylogPlugin;
use Spatie\Activitylog\Models\Activity;

class ActivitylogForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    TextInput::make('causer_id')
                        ->afterStateHydrated(function ($component, ?Model $record) {
                            return $component->state($record?->causer?->name ?? '-');
                        })
                        ->label(__('activitylog::forms.fields.causer.label')),

                    TextInput::make('subject_type')
                        ->afterStateHydrated(function ($component, ?Model $record, $state) {
                            /** @var Activity $record */
                            return $state ? $component->state(Str::of($state)->afterLast('\\')->headline().' # '.$record->subject_id) : $component->state('-');
                        })
                        ->label(__('activitylog::forms.fields.subject_type.label')),

                    Textarea::make('description')
                        ->label(__('activitylog::forms.fields.description.label'))
                        ->rows(2)
                        ->columnSpan('full'),
                ]),

                Section::make([
                    TextEntry::make('log_name')
                        ->content(function (?Model $record): string {
                            /** @var Activity $record */
                            return $record?->log_name ? ucwords($record->log_name) : '-';
                        })
                        ->label(__('activitylog::forms.fields.log_name.label')),

                    TextEntry::make('event')
                        ->content(function (?Model $record): string {
                            /** @var Activity $record */
                            return $record?->event ? ucwords(__('activitylog::action.event.'.$record->event)) : '-';
                        })
                        ->label(__('activitylog::forms.fields.event.label')),

                    TextEntry::make('created_at')
                        ->label(__('activitylog::forms.fields.created_at.label'))
                        ->content(function (?Model $record): string {
                            /** @var Activity $record */
                            if (! $record?->created_at) {
                                return '-';
                            }

                            $parser = ActivitylogPlugin::get()->getDateParser();

                            return $parser($record->created_at)
                                ->format(ActivitylogPlugin::get()->getDatetimeFormat());
                        }),
                ]),
            ]);
    }
}
