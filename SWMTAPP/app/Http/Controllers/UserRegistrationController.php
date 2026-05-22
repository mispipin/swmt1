<?php

namespace App\Http\Controllers;

use App\Models\TeacherClass;
use App\Models\TestRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class UserRegistrationController extends Controller
{
    public function showForm(): View|RedirectResponse
    {
        if ($redirect = $this->requireStudent()) {
            return $redirect;
        }

        $student = Auth::user();
        $inProgressRegistration = TestRegistration::query()
            ->where('user_id', $student->id)
            ->whereNull('total_poin')
            ->latest('created_at')
            ->first();

        if ($inProgressRegistration) {
            return redirect()
                ->route('test.start', $inProgressRegistration)
                ->with('success', 'Tes sebelumnya belum selesai. Sistem mengarahkan ke sesi terakhir kamu.');
        }

        $registrationMode = session('registration_mode', 'independent');
        $classCode = session('pending_class_code');

        return view('user.register-test', [
            'registrationMode' => $registrationMode,
            'classCode' => $classCode,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $student = Auth::user();
        if ($redirect = $this->requireStudent()) {
            return $redirect;
        }

        $inProgressRegistration = TestRegistration::query()
            ->where('user_id', $student->id)
            ->whereNull('total_poin')
            ->latest('created_at')
            ->first();

        if ($inProgressRegistration) {
            return redirect()
                ->route('test.start', $inProgressRegistration)
                ->with('success', 'Tes sebelumnya belum selesai. Sistem mengarahkan ke sesi terakhir kamu.');
        }

        $registrationMode = $request->session()->get('registration_mode', 'independent');
        $classCodeFromSession = $request->session()->get('pending_class_code');

        $validated = $request->validate([
            'school' => ['required', 'string', 'max:255'],
            'class_code' => ['nullable', 'string', 'max:20'],
            'class_name' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],
        ]);

        if ($registrationMode === 'with_code') {
            $validated['class_code'] = strtoupper(trim((string) ($classCodeFromSession ?: $validated['class_code'])));

            if ($validated['class_code'] === '') {
                return redirect()->route('student.start.with-code')
                    ->withErrors(['class_code' => 'Masukkan kode kelas terlebih dahulu.']);
            }
        } else {
            $validated['class_code'] = '';
        }

        $teacherClassId = null;

        if (!empty($validated['class_code'])) {
            $teacherClass = TeacherClass::query()
                ->where('code', strtoupper(trim($validated['class_code'])))
                ->first();

            if (!$teacherClass) {
                return back()
                    ->withInput()
                    ->withErrors(['class_code' => 'Kode kelas tidak ditemukan. Silakan cek kembali kode dari guru.']);
            }
            $teacherClassId = $teacherClass->id;
        }

        $registration = TestRegistration::create([
            'school' => $validated['school'],
            'class_name' => $validated['class_name'],
            'name' => $validated['name'],
            'birth_date' => $validated['birth_date'],
            'address' => '-', 
            'teacher_class_id' => $teacherClassId,
            'user_id' => $student->id,
        ]);

        $request->session()->forget(['registration_mode', 'pending_class_code']);

        return redirect()
            ->route('test.guide', $registration);
    }

    public function showGuide(TestRegistration $registration): View|RedirectResponse
    {
        if ($redirect = $this->authorizeStudentRegistration($registration)) {
            return $redirect;
        }

        return view('user.test-guide', compact('registration'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('student.login');
    }

    public function startTest(TestRegistration $registration): View|RedirectResponse
    {
        if ($redirect = $this->authorizeStudentRegistration($registration)) {
            return $redirect;
        }

        // Scan folder People dan Buah untuk membentuk 15 bagian test.
        $peoplePath = public_path('images/People');
        $fruitPath = public_path('images/Buah');
        $images = [];
        $fruitFiles = [];
        
        if (is_dir($peoplePath)) {
            $files = array_diff(scandir($peoplePath), ['.', '..']);
            $images = array_values(array_filter($files, function ($file) use ($peoplePath) {
                return is_file($peoplePath . '/' . $file);
            }));
        }

        if (is_dir($fruitPath)) {
            $files = array_diff(scandir($fruitPath), ['.', '..']);
            $fruitFiles = array_values(array_filter($files, function ($file) use ($fruitPath) {
                return is_file($fruitPath . '/' . $file);
            }));
        }
        
        $currentStage = 1;
        $totalStages = 15;
        $sections = [];

        $formatFruitName = function (string $file): string {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $name = preg_replace('/[_\-]+/', ' ', $name);
            $name = preg_replace('/\s+/', ' ', trim($name));

            return strtoupper($name);
        };

        $fruitLabels = array_values(array_unique(array_map($formatFruitName, $fruitFiles)));

        $buildFruitSlide = function () use ($fruitFiles, $fruitLabels, $formatFruitName) {
            $defaultImage = 'images/Buah/Lemon.png';
            $selectedFile = !empty($fruitFiles) ? $fruitFiles[array_rand($fruitFiles)] : 'Lemon.png';
            $selectedImage = !empty($fruitFiles) ? 'images/Buah/' . $selectedFile : $defaultImage;
            $correctChoice = $formatFruitName($selectedFile);

            $wrongPool = array_values(array_filter($fruitLabels, function ($label) use ($correctChoice) {
                return $label !== $correctChoice;
            }));

            if (empty($wrongPool)) {
                $fallback = $correctChoice === 'APEL' ? 'MANGGA' : 'APEL';
                $wrongPool = [$fallback];
            }

            $wrongChoice = $wrongPool[array_rand($wrongPool)];
            $choices = [$correctChoice, $wrongChoice];
            shuffle($choices);

            return [
                'type' => 'fruit',
                'prompt' => 'Buah apakah ini?',
                'image' => $selectedImage,
                'choices' => $choices,
            ];
        };

        if (!empty($images)) {
            $relativeImages = array_map(function ($file) {
                return 'images/People/' . $file;
            }, $images);

            for ($stage = 1; $stage <= $totalStages; $stage++) {
                $shuffledPeople = $relativeImages;
                shuffle($shuffledPeople);
                $recallTargets = array_slice($shuffledPeople, 0, min(2, count($shuffledPeople)));

                if (count($recallTargets) < 2) {
                    while (count($recallTargets) < 2) {
                        $recallTargets[] = $relativeImages[array_rand($relativeImages)];
                    }
                }

                $stageSlides = [];

                // Orang-buah diulang 2x: [orang, buah, orang, buah]
                for ($cycle = 0; $cycle < 2; $cycle++) {
                    $stageSlides[] = [
                        'type' => 'person',
                        'prompt' => 'Ingat wajah dibawah ini!',
                        'image' => $recallTargets[$cycle],
                    ];
                    $stageSlides[] = $buildFruitSlide();
                }

                $recallPool = array_values(array_unique(array_merge($recallTargets, $relativeImages)));
                $recallPool = array_values(array_filter($recallPool, function ($image) use ($recallTargets) {
                    return !in_array($image, $recallTargets, true);
                }));

                shuffle($recallPool);
                $distractors = array_slice($recallPool, 0, 4);
                $recallOptions = array_values(array_unique(array_merge($recallTargets, $distractors)));
                shuffle($recallOptions);

                $sections[] = [
                    'slides' => $stageSlides,
                    'recall_targets' => $recallTargets,
                    'recall_options' => $recallOptions,
                ];
            }
        } else {
            for ($stage = 1; $stage <= $totalStages; $stage++) {
                $stageSlides = [];
                for ($cycle = 0; $cycle < 4; $cycle++) {
                    $stageSlides[] = $buildFruitSlide();
                }

                $sections[] = [
                    'slides' => $stageSlides,
                    'recall_targets' => [],
                    'recall_options' => [],
                ];
            }
        }

        if (is_array($registration->progress_sections) && count($registration->progress_sections) > 0) {
            $sections = $registration->progress_sections;
        }

        $serverProgress = null;
        if (!is_null($registration->progress_current_section) && is_array($registration->progress_sections)) {
            $serverProgress = [
                'currentSectionIndex' => (int) $registration->progress_current_section,
                'currentSlideIndex' => (int) ($registration->progress_current_slide ?? 0),
                'uiStage' => (string) ($registration->progress_ui_stage ?? 'slide'),
                'pickedOrder' => is_array($registration->progress_picked_order) ? $registration->progress_picked_order : [],
                'sectionResults' => is_array($registration->progress_section_results) ? $registration->progress_section_results : [],
                'sections' => is_array($registration->progress_sections) ? $registration->progress_sections : [],
                'updatedAt' => optional($registration->progress_updated_at)->toIso8601String(),
            ];
        }

        $firstSection = $sections[0] ?? ['slides' => []];
        $firstSlide = $firstSection['slides'][0] ?? null;
        $randomImage = $firstSlide['image'] ?? null;
        $stagePrompt = $firstSlide['prompt'] ?? 'Ingat gambar dibawah ini!';
        
        return view('user.test-display-new', compact(
            'registration',
            'randomImage',
            'currentStage',
            'totalStages',
            'sections',
            'stagePrompt',
            'serverProgress',
        ));
    }

    public function updateProgress(Request $request, TestRegistration $registration): JsonResponse
    {
        if ($this->authorizeStudentRegistration($registration)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'current_section_index' => ['required', 'integer', 'min:0'],
            'current_slide_index' => ['required', 'integer', 'min:0'],
            'ui_stage' => ['nullable', 'string', 'max:20'],
            'picked_order' => ['nullable', 'array'],
            'picked_order.*' => ['string'],
            'section_results' => ['nullable', 'array'],
            'sections' => ['required', 'array'],
            'updated_at' => ['nullable', 'string'],
        ]);

        $registration->update([
            'progress_current_section' => $validated['current_section_index'],
            'progress_current_slide' => $validated['current_slide_index'],
            'progress_ui_stage' => $validated['ui_stage'] ?? 'slide',
            'progress_picked_order' => $validated['picked_order'] ?? [],
            'progress_section_results' => $validated['section_results'] ?? [],
            'progress_sections' => $validated['sections'],
            'progress_updated_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function showFruitStage(TestRegistration $registration): View|RedirectResponse
    {
        if ($redirect = $this->authorizeStudentRegistration($registration)) {
            return $redirect;
        }

        $currentStage = 1;
        $totalStages = 15;
        $fruitImage = 'images/Buah/Jeruk.png';

        return view('user.test-fruit', compact('registration', 'currentStage', 'totalStages', 'fruitImage'));
    }

    public function showResult(Request $request, TestRegistration $registration): View|RedirectResponse
    {
        if ($redirect = $this->authorizeStudentRegistration($registration)) {
            return $redirect;
        }

        // Jika data sudah ada di database (sudah pernah disave), gunakan data tersebut.
        // Jika belum ada, ambil dari query parameter dan simpan ke database.
        
        if ($registration->total_poin !== null && !$request->has('total_poin')) {
            $totalPoin = $registration->total_poin;
            $orangBenar = $registration->orang_benar;
            $urutanBenar = $registration->urutan_benar;
            $orangSalah = $registration->orang_salah;
            $urutanSalah = $registration->urutan_salah;
            $totalBagian = 15; // Default

            if (!is_null($registration->progress_current_section)) {
                $registration->update([
                    'progress_current_section' => null,
                    'progress_current_slide' => null,
                    'progress_ui_stage' => null,
                    'progress_picked_order' => null,
                    'progress_section_results' => null,
                    'progress_sections' => null,
                    'progress_updated_at' => null,
                ]);
            }
        } else {
            $totalBagian = max(1, (int) $request->query('total_bagian', 15));
            $maksOrang = $totalBagian * 2;

            $orangBenar = max(0, min($maksOrang, (int) $request->query('orang_benar', 0)));
            $urutanBenar = max(0, min($orangBenar, (int) $request->query('urutan_benar', 0)));

            $orangSalah = max(0, $maksOrang - $orangBenar);
            $urutanSalah = max(0, $orangBenar - $urutanBenar);

            $totalPoin = max(0, min($totalBagian * 20, (int) $request->query('total_poin', 0)));

            // Hanya update jika poin belum disave atau jika ini adalah hit pertama dari test yang baru selesai
            $registration->update([
                'orang_benar' => $orangBenar,
                'urutan_benar' => $urutanBenar,
                'orang_salah' => $orangSalah,
                'urutan_salah' => $urutanSalah,
                'total_poin' => $totalPoin,
                'tested_at' => now(),
                'progress_current_section' => null,
                'progress_current_slide' => null,
                'progress_ui_stage' => null,
                'progress_picked_order' => null,
                'progress_section_results' => null,
                'progress_sections' => null,
                'progress_updated_at' => null,
            ]);
        }

        $kategori = $this->getKategori($totalPoin);
        $kategoriSkor = $kategori['kategori'];
        $deskripsiKategori = $kategori['deskripsi'];

        return view('user.test-result', compact(
            'registration',
            'orangBenar',
            'urutanBenar',
            'orangSalah',
            'urutanSalah',
            'totalPoin',
            'totalBagian',
            'kategoriSkor',
            'deskripsiKategori'
        ));
    }

    public function exportResultPdf(TestRegistration $registration)
    {
        if ($redirect = $this->authorizeStudentRegistration($registration)) {
            return $redirect;
        }

        if ($registration->total_poin === null) {
            abort(404, 'Data test belum tersedia.');
        }

        $kategori = $this->getKategori($registration->total_poin);
        
        $pdf = Pdf::loadView('user.result-pdf', [
            'registration' => $registration,
            'kategori' => $kategori['kategori'],
            'deskripsi' => $kategori['deskripsi']
        ]);

        return $pdf->download('Hasil_Test_SWMT_' . Str::slug($registration->name) . '.pdf');
    }

    private function getKategori(?int $poin): array
    {
        $kategoriSkor = 'Perlu Latihan';
        $deskripsiKategori = 'Kemampuan memori spasial masih perlu banyak ditingkatkan';

        if ($poin >= 261) {
            $kategoriSkor = 'Luar Biasa';
            $deskripsiKategori = 'Kemampuan memori spasial sangat kuat dan cepat';
        } elseif ($poin >= 221) {
            $kategoriSkor = 'Sangat Baik';
            $deskripsiKategori = 'Sudah di atas rata-rata, cukup akurat dalam mengingat';
        } elseif ($poin >= 181) {
            $kategoriSkor = 'Baik';
            $deskripsiKategori = 'Kemampuan memori cukup bagus dan stabil';
        } elseif ($poin >= 121) {
            $kategoriSkor = 'Cukup';
            $deskripsiKategori = 'Sudah mulai memahami, tapi masih belum konsisten';
        }

        return [
            'kategori' => $kategoriSkor,
            'deskripsi' => $deskripsiKategori
        ];
    }

    private function studentLoggedIn(): bool
    {
        $user = Auth::user();

        return (bool) $user && $user->role === 'student';
    }

    private function requireStudent(): ?RedirectResponse
    {
        if (!$this->studentLoggedIn()) {
            Auth::logout();

            return redirect()->route('student.login');
        }

        return null;
    }

    private function authorizeStudentRegistration(TestRegistration $registration): ?RedirectResponse
    {
        if ($redirect = $this->requireStudent()) {
            return $redirect;
        }

        $student = Auth::user();
        if (!$student || $registration->user_id !== $student->id) {
            return redirect()->route('student.dashboard')
                ->withErrors(['login' => 'Data test tidak ditemukan untuk akun siswa ini.']);
        }

        return null;
    }
}
