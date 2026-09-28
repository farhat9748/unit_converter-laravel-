<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Unit Converter - Laravel</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js for lightweight reactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full flex flex-col justify-between text-slate-800">

    <div class="py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-xl mx-auto">
            
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Unit Converter</h1>
                <p class="mt-2 text-sm text-slate-600">Quickly convert between lengths, weights, and temperatures.</p>
            </div>

            <!-- Converter Card -->
            <div x-data="unitConverter()" x-init="convert()" class="bg-white shadow-xl rounded-2xl p-6 sm:p-8 border border-slate-100">
                
                <!-- Category Tabs -->
                <div class="flex rounded-xl bg-slate-100 p-1 mb-6">
                    <template x-for="cat in categories" :key="cat.id">
                        <button type="button" @click="setCategory(cat.id)"
                                :class="category === cat.id ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                class="flex-1 py-2 text-xs font-semibold rounded-lg transition-all"
                                x-text="cat.name">
                        </button>
                    </template>
                </div>

                <!-- Form / Inputs -->
                <form @submit.prevent="convert" class="space-y-5">
                    
                    <!-- Value Input -->
                    <div>
                        <label for="value" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Value</label>
                        <input type="number" step="any" id="value" x-model.number="value" @input="convert"
                               class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg px-4 py-2.5 border"
                               placeholder="Enter value...">
                    </div>

                    <!-- From / To Selectors -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="from" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">From</label>
                            <select id="from" x-model="fromUnit" @change="convert"
                                    class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2.5 border bg-white">
                                <template x-for="(label, key) in currentUnits" :key="key">
                                    <option :value="key" x-text="label"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label for="to" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">To</label>
                            <select id="to" x-model="toUnit" @change="convert"
                                    class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2.5 border bg-white">
                                <template x-for="(label, key) in currentUnits" :key="key">
                                    <option :value="key" x-text="label"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- Swap Button -->
                    <div class="flex justify-end">
                        <button type="button" @click="swapUnits" class="text-xs font-medium text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                            ⇄ Swap units
                        </button>
                    </div>

                    <!-- Result Box -->
                    <div class="mt-6 rounded-xl bg-indigo-50 p-5 border border-indigo-100 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-indigo-500 uppercase tracking-wider">Converted Result</span>
                            <div class="text-2xl font-bold text-indigo-900 mt-0.5" x-text="resultText"></div>
                        </div>
                    </div>

                </form>

            </div>

        </div>
    </div>

    <!-- Alpine Logic -->
    <script>
        function unitConverter() {
            return {
                category: 'length',
                value: 1,
                fromUnit: 'm',
                toUnit: 'km',
                resultText: '0.001 km',
                categories: [
                    { id: 'length', name: 'Length' },
                    { id: 'weight', name: 'Weight' },
                    { id: 'temperature', name: 'Temperature' }
                ],
                units: {
                    length: { m: 'Meters (m)', km: 'Kilometers (km)', cm: 'Centimeters (cm)', mm: 'Millimeters (mm)', mi: 'Miles (mi)', ft: 'Feet (ft)' },
                    weight: { kg: 'Kilograms (kg)', g: 'Grams (g)', mg: 'Milligrams (mg)', lb: 'Pounds (lb)', oz: 'Ounces (oz)' },
                    temperature: { c: 'Celsius (°C)', f: 'Fahrenheit (°F)', k: 'Kelvin (K)' }
                },
                get currentUnits() {
                    return this.units[this.category];
                },
                setCategory(cat) {
                    this.category = cat;
                    const keys = Object.keys(this.currentUnits);
                    this.fromUnit = keys[0];
                    this.toUnit = keys[1] || keys[0];
                    this.convert();
                },
                swapUnits() {
                    const temp = this.fromUnit;
                    this.fromUnit = this.toUnit;
                    this.toUnit = temp;
                    this.convert();
                },
                convert() {
                    if (isNaN(this.value)) {
                        this.resultText = 'Invalid input';
                        return;
                    }
                    if (this.category === 'temperature') {
                        let res = this.convertTemp(this.value, this.fromUnit, this.toUnit);
                        this.resultText = `${res.toFixed(4)} ${this.toUnit.toUpperCase()}`;
                        return;
                    }
                    const baseMultipliers = {
                        m: 1, km: 1000, cm: 0.01, mm: 0.001, mi: 1609.34, ft: 0.3048,
                        kg: 1, g: 0.001, mg: 0.000001, lb: 0.453592, oz: 0.0283495
                    };
                    const baseVal = this.value * (baseMultipliers[this.fromUnit] || 1);
                    const finalVal = baseVal / (baseMultipliers[this.toUnit] || 1);
                    this.resultText = `${Number.isInteger(finalVal) ? finalVal : finalVal.toFixed(4)} ${this.toUnit}`;
                },
                convertTemp(val, from, to) {
                    if (from === to) return val;
                    let celsius = val;
                    if (from === 'f') celsius = (val - 32) * 5/9;
                    if (from === 'k') celsius = val - 273.15;
                    
                    if (to === 'c') return celsius;
                    if (to === 'f') return (celsius * 9/5) + 32;
                    if (to === 'k') return celsius + 273.15;
                    return val;
                }
            }
        }
    </script>
</body>
</html>