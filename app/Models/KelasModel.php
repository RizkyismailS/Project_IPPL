<?php

namespace App\Models;

use CodeIgniter\Model;

class KelasModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'kelas';
    protected $primaryKey       = 'kode_kelas';
    protected $useAutoIncrement = false; // kode_kelas kemungkinan diinput/digenerate
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'kode_kelas', 
        'nama_kelas', 
        'kode_matakuliah', 
        'dosen_nip', 
        'hari', 
        'waktu_mulai_kelas', 
        'waktu_selesai_kelas', 
        'ruangan', 
        'tahun_ajaran', 
        'semester', 
        'kode_enrollment',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'kode_kelas'        => 'required|alpha_numeric_dash|max_length[20]|is_unique[kelas.kode_kelas]',
        'nama_kelas'        => 'required|string|max_length[100]',
        'kode_matakuliah'   => 'required|exists[matakuliah.kode_matakuliah]',
        'dosen_nip'         => 'required|exists[dosen.nip]',
        'hari'              => 'required|in_list[Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu]',
        'waktu_mulai_kelas' => 'required|valid_time', 
        'waktu_selesai_kelas' => 'required|valid_time',
        'ruangan'           => 'permit_empty|string|max_length[50]',
        'tahun_ajaran'      => 'required|string|max_length[10]',
        'semester'          => 'required|string|max_length[20]', 
        'kode_enrollment'   => 'permit_empty|alpha_numeric|max_length[20]|is_unique[kelas.kode_enrollment]', 
    ];
    protected $validationMessages   = [
        'kode_kelas' => [
            'required'  => 'Kode kelas wajib diisi.',
            'is_unique' => 'Kode kelas ini sudah digunakan.',
            'max_length'=> 'Kode kelas maksimal 20 karakter.'
        ],
        'nama_kelas' => [
            'required' => 'Nama kelas wajib diisi.'
        ],
        'kode_matakuliah' => [
            'required' => 'Mata kuliah wajib dipilih.',
            'exists'   => 'Mata kuliah yang dipilih tidak valid atau tidak ada di database.'
        ],
        'dosen_nip' => [
            'required' => 'Dosen pengampu wajib dipilih.',
            'exists'   => 'Dosen pengampu yang dipilih tidak valid atau tidak ada di database.'
        ],
        'hari' => [
            'required' => 'Hari wajib dipilih.',
            'in_list'  => 'Pilihan hari tidak valid.'
        ],
        'waktu_mulai_kelas' => [
            'required'   => 'Waktu mulai kelas wajib diisi.',
            'valid_time' => 'Format waktu mulai kelas tidak valid.'
        ],
        'waktu_selesai_kelas' => [
            'required'   => 'Waktu selesai kelas wajib diisi.',
            'valid_time' => 'Format waktu selesai kelas tidak valid.'
        ],
        'kode_enrollment' => [
            'is_unique' => 'Kode enrollment ini sudah digunakan oleh kelas lain.',
            'max_length'=> 'Kode enrollment maksimal 20 karakter.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    // Tidak ada callback khusus yang umum di sini, kecuali ada logika pra-proses data tertentu

    // --- Fungsi Kustom untuk Mendapatkan Data Kelas dengan Detail ---

    /**
     * Mengambil detail satu kelas beserta nama mata kuliah dan nama dosen.
     * @param string $kodeKelas
     * @return array|null
     */
    public function getKelasDetail(string $kodeKelas): ?array
    {
        return $this->select('kelas.*, matakuliah.nama_matakuliah, matakuliah.sks, dosen.nama as nama_dosen')
                    ->join('matakuliah', 'matakuliah.kode_matakuliah = kelas.kode_matakuliah', 'left')
                    ->join('dosen', 'dosen.nip = kelas.dosen_nip', 'left')
                    ->where('kelas.kode_kelas', $kodeKelas)
                    ->first();
    }

    /**
     * Mengambil semua kelas dengan detail (nama mata kuliah, nama dosen) dengan pagination.
     * Bisa difilter berdasarkan NIP dosen.
     * @param int $perPage Jumlah item per halaman
     * @param string $group Nama grup pager
     * @param string|null $dosenNip Filter berdasarkan NIP dosen (opsional)
     * @param string|null $searchTerm Kata kunci pencarian untuk nama kelas atau nama mata kuliah (opsional)
     * @param string|null $filterTahunAjaran Filter berdasarkan tahun ajaran (opsional)
     * @param string|null $filterSemester Filter berdasarkan semester (opsional)
     * @return array
     */
    public function getAllKelasWithDetail(
        int $perPage = 10, 
        string $group = 'kelas_dosen', // Beri nama grup pager yang unik
        ?string $dosenNip = null, 
        ?string $searchTerm = null,
        ?string $filterTahunAjaran = null,
        ?string $filterSemester = null
    ): array
    {
        $builder = $this->select('kelas.*, matakuliah.nama_matakuliah, matakuliah.sks, dosen.nama as nama_dosen, dosen.email as email_dosen')
                        ->join('matakuliah', 'matakuliah.kode_matakuliah = kelas.kode_matakuliah', 'left')
                        ->join('dosen', 'dosen.nip = kelas.dosen_nip', 'left');
        
        if ($dosenNip !== null && !empty($dosenNip)) {
            $builder->where('kelas.dosen_nip', $dosenNip);
        }

        if ($searchTerm !== null && !empty($searchTerm)) {
            $builder->groupStart()
                        ->like('kelas.nama_kelas', $searchTerm)
                        ->orLike('matakuliah.nama_matakuliah', $searchTerm)
                        ->orLike('dosen.nama', $searchTerm)
                    ->groupEnd();
        }

        if ($filterTahunAjaran !== null && !empty($filterTahunAjaran)) {
            $builder->where('kelas.tahun_ajaran', $filterTahunAjaran);
        }

        if ($filterSemester !== null && !empty($filterSemester)) {
            $builder->where('kelas.semester', $filterSemester);
        }
        
        $builder->orderBy('kelas.tahun_ajaran', 'DESC')
                ->orderBy('kelas.semester', 'DESC')
                ->orderBy('kelas.nama_kelas', 'ASC');

        return $builder->paginate($perPage, $group);
    }

    /**
     * Mencari kelas berdasarkan kode enrollment.
     * @param string $kodeEnrollment
     * @return array|null
     */
    public function findByKodeEnrollment(string $kodeEnrollment): ?array
    {
        if (empty($kodeEnrollment)) {
            return null;
        }
        return $this->where('kode_enrollment', $kodeEnrollment)->first();
    }

    /**
     * Mendapatkan daftar tahun ajaran unik dari tabel kelas.
     * @return array
     */
    public function getDistinctTahunAjaran(): array
    {
        return $this->distinct()->select('tahun_ajaran')->orderBy('tahun_ajaran', 'DESC')->findAll();
    }

    /**
     * Mendapatkan daftar semester unik dari tabel kelas.
     * @return array
     */
    public function getDistinctSemester(): array
    {
        return $this->distinct()->select('semester')->orderBy('semester', 'ASC')->findAll();
    }
}