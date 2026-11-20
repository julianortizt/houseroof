<?php

namespace App\Livewire;

use App\Models\Quote;
use Livewire\Component;

class QuoteForm extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $company = '';
    public string $address = '';
    public string $city = '';
    public string $postal_code = '';
    public string $service_type = 'resin';
    public ?float $area_m2 = null;
    public string $description = '';
    public bool $submitted = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'service_type' => 'required|string|in:resin,granite,other',
            'area_m2' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:5000',
        ];
    }

    public function submit(): void
    {
        $this->validate();

        Quote::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'company' => $this->company ?: null,
            'address' => $this->address ?: null,
            'city' => $this->city ?: null,
            'postal_code' => $this->postal_code ?: null,
            'service_type' => $this->service_type,
            'area_m2' => $this->area_m2,
            'description' => $this->description ?: null,
            'status' => 'new',
        ]);

        $this->submitted = true;

        session()->flash('success', 'Su solicitud de cotización ha sido enviada correctamente. Nos pondremos en contacto pronto.');
    }

    public function render()
    {
        return view('livewire.quote-form');
    }
}
