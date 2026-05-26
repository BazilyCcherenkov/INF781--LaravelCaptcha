<x-guest-layout>
    @if (session('status'))
        <div class="mb-4 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('contact.store') }}">
        @csrf

        <div style="position:absolute; left:-9999px; top:-9999px;" aria-hidden="true">
            <label for="website">Sitio web</label>
            <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
        </div>

        <div>
            <x-input-label for="name" value="Nombre" />
            <x-text-input id="name" name="name" type="text" required />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" value="Correo" />
            <x-text-input id="email" name="email" type="email" required />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="message" value="Mensaje" />
            <textarea id="message" name="message" rows="5" class="block w-full border-gray-300 rounded-md shadow-sm"></textarea>
            <x-input-error :messages="$errors->get('message')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="captcha" value="Código de verificación" />
            <img src="{{ captcha_src('default') }}" id="cap-img" class="mt-1" />
            <x-text-input id="captcha" name="captcha" type="text" required />
            <x-input-error :messages="$errors->get('captcha')" class="mt-2" />
        </div>

        <x-primary-button class="mt-4">Enviar</x-primary-button>
    </form>
</x-guest-layout>