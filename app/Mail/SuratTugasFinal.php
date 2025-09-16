<?php

namespace App\Mail;

use App\Models\TugasHeader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SuratTugasFinal extends Mailable implements ShouldQueue // Implementasi ShouldQueue penting!
{
    use Queueable, SerializesModels;

    public $tugas;
    public $subjectLine;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(TugasHeader $tugas)
    {
        $this->tugas = $tugas;
        $this->subjectLine = "Surat Tugas Baru Terbit: " . ($tugas->nama_umum ?? $tugas->tugas);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $emailView = $this->subject($this->subjectLine)
                          ->view('emails.surat_tugas.final') // Pastikan Anda punya view ini
                          ->with([
                              'namaSurat' => $this->tugas->nama_umum ?? $this->tugas->tugas,
                              'nomorSurat' => $this->tugas->nomor,
                              'urlSurat' => route('surat_tugas.show', $this->tugas->id),
                          ]);

        // Jika PDF yang ditandatangani ada, lampirkan
        if ($this->tugas->signed_pdf_path && Storage::disk('local')->exists($this->tugas->signed_pdf_path)) {
            
            $safeNomor = preg_replace('/[\/\\\\]+/', '-', (string)($this->tugas->nomor ?? 'SuratTugas'));
            
            $emailView->attachFromStorage(
                $this->tugas->signed_pdf_path,
                'SuratTugas_' . $safeNomor . '.pdf',
                [
                    'mime' => 'application/pdf',
                ]
            );
        }

        return $emailView;
    }
}