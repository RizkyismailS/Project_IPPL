<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MataKuliahModel;

class MatakuliahController extends BaseController
{
    protected $mataKuliahModel;

    public function __construct()
    {
        $this->mataKuliahModel = new MataKuliahModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Daftar Mata Kuliah',
            'matakuliah' => $this->mataKuliahModel->findAll()
        ];
        return view('admin/matakuliah/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Mata Kuliah',
            'validation' => \Config\Services::validation()
        ];
        return view('admin/matakuliah/create', $data);
    }

    public function store()
    {
        if (!$this->validate($this->mataKuliahModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $this->mataKuliahModel->save([
            'kode_matakuliah' => $this->request->getPost('kode_matakuliah'),
            'nama_matakuliah' => $this->request->getPost('nama_matakuliah'),
            'sks' => $this->request->getPost('sks'),
        ]);

        return redirect()->to('/admin/matakuliah')->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function edit($kode)
    {
        $matakuliah = $this->mataKuliahModel->find($kode);
        if (!$matakuliah) {
            return redirect()->to('/admin/matakuliah')->with('error', 'Data tidak ditemukan.');
        }
        $data = [
            'title' => 'Edit Mata Kuliah',
            'matakuliah' => $matakuliah,
            'validation' => \Config\Services::validation()
        ];
        return view('admin/matakuliah/edit', $data);
    }

    public function update($kode)
    {
        // If the kode_matakuliah is not changed, we don't need to check uniqueness
        if ($this->request->getPost('kode_matakuliah') !== $kode) {
            // If kode is being changed, we need to validate it's unique
            if (
                !$this->validate([
                    'kode_matakuliah' => 'required|alpha_numeric|max_length[15]|is_unique[matakuliah.kode_matakuliah]',
                    'nama_matakuliah' => 'required|string|max_length[100]',
                    'sks' => 'permit_empty|integer|greater_than_equal_to[0]',
                ])
            ) {
                return redirect()->back()->withInput()->with('validation', $this->validator);
            }

            // Update with new kode
            $data = [
                'kode_matakuliah' => $this->request->getPost('kode_matakuliah'),
                'nama_matakuliah' => $this->request->getPost('nama_matakuliah'),
                'sks' => $this->request->getPost('sks'),
            ];

            $this->mataKuliahModel->delete($kode); // Remove old record
            $this->mataKuliahModel->insert($data); // Insert new record
        } else {
            // Kode not changed, just validate other fields
            if (
                !$this->validate([
                    'nama_matakuliah' => 'required|string|max_length[100]',
                    'sks' => 'permit_empty|integer|greater_than_equal_to[0]',
                ])
            ) {
                return redirect()->back()->withInput()->with('validation', $this->validator);
            }

            // Update without changing kode
            $this->mataKuliahModel->update($kode, [
                'nama_matakuliah' => $this->request->getPost('nama_matakuliah'),
                'sks' => $this->request->getPost('sks'),
            ]);
        }

        return redirect()->to('/admin/matakuliah')->with('success', 'Mata kuliah berhasil diupdate.');
    }

    public function delete($kode)
    {
        $this->mataKuliahModel->delete($kode);
        return redirect()->to('/admin/matakuliah')->with('success', 'Mata kuliah berhasil dihapus.');
    }
}