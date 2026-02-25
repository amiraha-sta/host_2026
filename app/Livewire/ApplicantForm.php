<?php

namespace App\Livewire;

use App\Models\Applicant;
use Illuminate\Support\Facades\Http;
use M21\Formkit\Support\FormQuestionBuilder;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('vendor.formkit.components.layouts.portal')]
class ApplicantForm extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    // Step 1: Personal Information
                    Step::make('Personal Information')
                        ->description('Your details and contact information')
                        ->schema([
                            Group::make([FormQuestionBuilder::make(TextInput::class, 'APL_FName'), FormQuestionBuilder::make(TextInput::class, 'APL_MName', false), FormQuestionBuilder::make(TextInput::class, 'APL_LName')])
                                ->columns(3)
                                ->columnSpanFull(),
                            Group::make([
                                FormQuestionBuilder::make(TextInput::class, 'APL_Address_1'),
                                FormQuestionBuilder::make(TextInput::class, 'APL_Address_2'),
                                FormQuestionBuilder::make(Select::class, 'APL_Area')->options([
                                    'Arima' => 'Arima',
                                    'Chaguanas' => 'Chaguanas',
                                    'Couva–Tabaquite–Talparo' => 'Couva–Tabaquite–Talparo',
                                    'Diego Martin' => 'Diego Martin',
                                    'Penal–Debe' => 'Penal–Debe',
                                    'Point Fortin' => 'Point Fortin',
                                    'Port of Spain City' => 'Port of Spain City',
                                    'Princes Town' => 'Princes Town',
                                    'Mayaro / Rio Claro' => 'Mayaro / Rio Claro',
                                    'San Fernando City' => 'San Fernando City',
                                    'San Juan–Laventille' => 'San Juan–Laventille',
                                    'Sangre Grande' => 'Sangre Grande',
                                    'Siparia' => 'Siparia',
                                    'Tunapuna–Piarco' => 'Tunapuna–Piarco',
                                    'Other' => 'Other',
                                ]),
                            ])
                                ->columns(2)
                                ->columnSpanFull(),
                            FormQuestionBuilder::make(Select::class, 'APL_Gender')->options([
                                'Male' => 'Male',
                                'Female' => 'Female',
                            ]),
                            FormQuestionBuilder::make(TextInput::class, 'APL_Email')->email(),
                            FormQuestionBuilder::make(TextInput::class, 'APL_PPhone')->tel()->telRegex('/^[0-9]{3}-[0-9]{4}$|^[0-9]{3}-[0-9]{3}-[0-9]{4}$/'),
                            FormQuestionBuilder::make(TextInput::class, 'APL_APhone', false)->tel()->telRegex('/^[0-9]{3}-[0-9]{4}$|^[0-9]{3}-[0-9]{3}-[0-9]{4}$/'),
                            FormQuestionBuilder::make(DatePicker::class, 'APL_DOB'),
                            FormQuestionBuilder::make(TextInput::class, 'APL_Nationality'),
                            Fieldset::make('Birth Certificate')
                                ->schema([FormQuestionBuilder::make(TextInput::class, 'APL_BIRTH_PIN'), FormQuestionBuilder::make(FileUpload::class, 'APL_Birth_File')->disk('public')->directory('applicants/birth-certificates')->maxSize(10240)->helperText('Please upload a valid image or PDF file. Size of image should not be more than 10MB.')])
                                ->columnSpanFull()
                                ->columns(1),
                            Fieldset::make('National Identification Card or Trinidad and Tobago Passport')
                                ->schema([
                                    FormQuestionBuilder::make(Select::class, 'APL_ID_TYP')->options([
                                        'National ID' => 'National ID',
                                        'Trinidad and Tobago Passport' => 'Trinidad and Tobago Passport',
                                    ]),
                                    FormQuestionBuilder::make(TextInput::class, 'APL_ID_Number'),
                                    FormQuestionBuilder::make(FileUpload::class, 'APL_ID_File')->disk('public')->directory('applicants/id-documents')->maxSize(10240)->helperText('Please upload a valid image or PDF file. Size of image should not be more than 10MB.'),
                                ])
                                ->columnSpanFull()
                                ->columns(1),
                        ])
                        ->columns(2),

                    // Step 2: Education & Skills Background
                    Step::make('Education & Skills Background')
                        ->description('Education & employment status')
                        ->schema([
                            FormQuestionBuilder::make(Select::class, 'APL_HLOE')->options([
                                'Primary School' => 'Primary School',
                                'Secondary School' => 'Secondary School',
                                'Tertiary Education' => 'Tertiary Education',
                                'Technical/Vocational' => 'Technical/Vocational',
                            ]),
                            FormQuestionBuilder::make(Select::class, 'APL_Employment_Status')->options([
                                'Employed Full Time' => 'Employed Full Time',
                                'Employed Part Time' => 'Employed Part Time',
                                'Self-Employed (Business Owner)' => 'Self-Employed (Business Owner)',
                                'Both Employed and Business Owner' => 'Both Employed and Business Owner',
                                'Student' => 'Student',
                                'Unemployed' => 'Unemployed',
                            ]),
                        ])
                        ->columns(2),

                    Step::make('Programme Interest')
                        ->description('Programme selection')
                        ->schema([
                            FormQuestionBuilder::make(Radio::class, 'APL_Previously_Participated')
                                ->options([
                                    '1' => 'Yes',
                                    '0' => 'No',
                                ])
                                ->inline()
                                ->live(),
                            FormQuestionBuilder::make(Textarea::class, 'APL_Previously_Participated_Details', false)->visible(fn(Get $get) => $get('APL_Previously_Participated') == '1')->required(fn(Get $get) => $get('APL_Previously_Participated') == '1')->columnSpanFull(),
                            \Filament\Schemas\Components\View::make('form.cohorts'),
                            FormQuestionBuilder::make(Select::class, 'APL_Programme')->options([
                                'Cohort 1' => 'Cohort 1',
                                'Cohort 2' => 'Cohort 2',
                                'Cohort 3' => 'Cohort 3',
                            ]),
                            FormQuestionBuilder::make(Radio::class, 'APL_Attend')
                                ->options([
                                    '1' => 'Yes',
                                    '0' => 'No',
                                ])
                                ->inline(),
                            FormQuestionBuilder::make(Radio::class, 'APL_Experience')
                                ->options([
                                    '1' => 'Yes',
                                    '0' => 'No',
                                ])
                                ->inline()
                                ->live(),

                            FormQuestionBuilder::make(Textarea::class, 'APL_Experience_Details', false)->visible(fn(Get $get) => $get('APL_Experience') == '1')->required(fn(Get $get) => $get('APL_Experience') == '1')->columnSpanFull(),

                            FormQuestionBuilder::make(Textarea::class, 'APL_Future_Plans')->columnSpanFull(),
                            FormQuestionBuilder::make(Select::class, 'APL_How_Found_Programme')
                                ->options([
                                    // social media,
                                    'Social Media' => 'Social Media',
                                    'Television/Radio/Newspaper advertisements' => 'Television/Radio/Newspaper advertisements',
                                    'Website' => 'Website',
                                    'Friend or Family Member' => 'Friend or Family Member',
                                    'WhatsApp' => 'WhatsApp',
                                    'Other' => 'Other',
                                ])
                                ->live(),
                            FormQuestionBuilder::make(TextInput::class, 'APL_How_Found_Programme_Other', false)->visible(fn(Get $get) => $get('APL_How_Found_Programme') === 'Other')->required(fn(Get $get) => $get('APL_How_Found_Programme') === 'Other'),
                        ]),

                    // Step 3: Consent & Submission
                    Step::make('Consent & Submission')
                        ->description('Review and consent')
                        ->schema([
                            \Filament\Schemas\Components\View::make('form.disabilityNotice'),
                            FormQuestionBuilder::make(Radio::class, 'APL_Disability_Status')
                                ->options([
                                    '1' => 'Yes',
                                    '0' => 'No',
                                ])
                                ->inline()
                                ->live(),
                            FormQuestionBuilder::make(Textarea::class, 'APL_Disability_Details', false)->visible(fn(Get $get) => $get('APL_Disability_Status') == '1')->required(fn(Get $get) => $get('APL_Disability_Status') == '1')->columnSpanFull(),

                            FormQuestionBuilder::make(Radio::class, 'APL_Consent_Followup')
                                ->options([
                                    '1' => 'Yes',
                                    '0' => 'No',
                                ])
                                ->inline(),
                            FormQuestionBuilder::make(Radio::class, 'APL_Subscribe_Mailing')
                                ->options([
                                    '1' => 'Yes',
                                    '0' => 'No',
                                ])
                                ->inline(),
                            FormQuestionBuilder::make(Radio::class, 'APL_Photo_Consent')
                                ->options([
                                    '1' => 'Yes',
                                    '0' => 'No',
                                ])
                                ->inline(),
                            Fieldset::make('NOTE')
                                ->schema([
                                    Text::make(new HtmlString('<strong>You must read and accept the following:</strong><br> I hereby declare that the information given in this application is true and correct to the best of my knowledge and belief. If any information given in this application proves to be false or incorrect, I accept the consequences of automatic rejection of the submission and that I may be liable for any breach of the applicable Laws of the Republic of Trinidad and Tobago.')),
                                    FormQuestionBuilder::make(Radio::class, 'APL_Accepts')
                                        ->options([
                                            '1' => 'Yes',
                                            '0' => 'No',
                                        ])
                                        ->inline(),
                                ])
                                ->columnSpanFull()
                                ->columns(1),
                        ]),
                ])->skippable(),
            ])
            ->statePath('data')
            ->model(Applicant::class);
    }

    public function create(): mixed
    {
        $data = $this->form->getState();

        $record = Applicant::create($data);

        $name = "{$record->APL_FName} {$record->APL_LName}";
        Http::withHeaders([
            'appID' => env('SWIFT_APP_ID'),
            'Authorization' => 'Bearer ' . env('SWIFT_TOKEN'),
        ])->post('https://swift.msya.gov.tt/api/general', [
            'email' => $record->APL_Email,
            'title' => 'Hospitality Operations and Service Training Notification System',
            'subject' => 'Hospitality Operations and Service Training Application (2026)',
            'name' => $name,
            'body' => 'This email serves to inform you that your application has been received.',
            'app' => 'HOIST 2026',
            'header' => "Thank you {$name}",
            'fromAddress' => 'youthinfo.msya@gov.tt',
            'fromName' => 'MSYA',
        ]);

        $programmeName = "Hospitality Operations and Service Training Programme 2025";
        return redirect("https://msya.gov.tt/thank-you/?FirstName={$name}&ProgrammeName=" . urlencode($programmeName));

        // return redirect("https://msya.gov.tt/thank-you/?FirstName={$name}&ProgrammeName=GERIATRIC%20ADOLESCENT%20PARTNERSHIP%20PROGRAMME%202025%20");

        // redirect()->route('application')->with('success', 'Application submitted successfully');
    }

    public function render(): View
    {
        return view('livewire.applicant-form');
    }
}
