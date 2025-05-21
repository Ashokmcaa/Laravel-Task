<?php
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\OTP;
use Illuminate\Support\Facades\RateLimiter;

new class extends Component {
    public $otp = ['', '', '', '', '', ''];
    public $type;
    public $error = null;
    public $loading = false;

    public function mount($type = 'email')
    {
        $this->type = in_array($type, ['email', 'sms']) ? $type : 'email';
        OTP::generateForUser(Auth::user(), $this->type);
        OTP::cleanupExpired();
    }

    public function verifyOTP(): void
    {
        $this->loading = true;
        $this->error = null;

        $key = 'otp_attempts:' . Auth::id();
        $maxAttempts = 5;
        $decayMinutes = 10;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $this->error = 'Too many attempts. Please wait ' . RateLimiter::availableIn($key) . ' seconds.';
            $this->loading = false;
            return;
        }

        $this->validate(
            [
                'otp' => ['required', 'array', 'size:6'],
                'otp.*' => ['required', 'numeric', 'digits:1'],
            ],
            [
                'otp.*.numeric' => 'Each digit must be a number.',
                'otp.*.digits' => 'Each field must be a single digit.',
            ],
        );

        $code = implode('', $this->otp);
        $otp = OTP::where('user_id', Auth::id())->where('type', $this->type)->whereNull('verified_at')->latest()->first();

        if (!$otp || !$otp->isValid($code, Auth::user())) {
            RateLimiter::increment($key, $decayMinutes * 60);
            $this->error = 'Invalid or expired OTP.';
            $this->loading = false;
            return;
        }

        $otp->verify();
        RateLimiter::clear($key);
        $this->dispatch('otp-verified');
        $this->loading = false;
    }
};
?>


<div x-data="{
    otp: ['', '', '', '', '', ''],
    focusNext(index) {
        if (index < 5 && this.otp[index].length === 1 && this.otp[index].match(/^[0-9]$/)) {
            this.$refs[`otp${index + 1}`].focus();
        }
        if (this.otp.every(digit => digit.length === 1 && digit.match(/^[0-9]$/))) {
            $wire.otp = this.otp;
            $wire.verifyOTP();
        }
    },
    focusPrev(index, event) {
        if (index > 0 && event.key === 'Backspace' && this.otp[index].length === 0) {
            this.$refs[`otp${index - 1}`].focus();
        }
    },
    handlePaste(event) {
        const paste = event.clipboardData.getData('text').replace(/\D/g, '');
        if (paste.length === 6) {
            this.otp = paste.split('');
            $wire.otp = this.otp;
            $wire.verifyOTP();
        }
    },
    handleInput(index, event) {
        $wire.otp[index] = event.target.value.replace(/\D/g, '');
        this.otp[index] = $wire.otp[index];
        this.focusNext(index);
    }
}" class="space-y-6">
    <!-- OTP Input Fields -->

    <div class="flex justify-center space-x-3">
        @for ($i = 0; $i < 6; $i++)
            <input
                style="width:40px;height:40px;text-align:center;font-size:18px;margin:0 5px;border:1px solid #ccc;border-radius:4px;outline:none; transition: border-color 0.3s;"
                x-ref="otp{{ $i }}" x-model="otp[{{ $i }}]"
                @input="handleInput({{ $i }}, $event)" @keydown="focusPrev({{ $i }}, $event)"
                @paste="handlePaste($event)" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                class="w-14 h-14 text-center text-xl font-semibold border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 shadow-sm hover:shadow-md"
                aria-label="OTP digit {{ $i + 1 }}" required>
        @endfor
    </div>

    <!-- Feedback Messages -->
    <div class="text-center">
        @if ($error)
            <p class="text-red-600 bg-red-100 px-4 py-2 rounded-lg inline-block shadow-sm animate-pulse" role="alert">
                {{ $error }}
            </p>
        @endif

        @if ($loading)
            <p class="text-blue-600 bg-blue-100 px-4 py-2 rounded-lg inline-block shadow-sm animate-pulse">
                Verifying... <span class="inline-block animate-spin">⏳</span>
            </p>
        @endif

        @if (!$error && !$loading)
            <p class="text-gray-500 text-sm">Enter the 6-digit OTP to verify</p>
        @endif
    </div>
</div>
