<?php

namespace App\Controllers\Admin;

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
            'sks'             => $this->request->getPost('sks'),
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
        $rules = $this->mataKuliahModel->getValidationRules();
        // Remove is_unique for kode_matakuliah on update
        unset($rules['kode_matakuliah']);

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $this->mataKuliahModel->update($kode, [
            'nama_matakuliah' => $this->request->getPost('nama_matakuliah'),
            'sks'             => $this->request->getPost('sks'),
        ]);

        return redirect()->to('/admin/matakuliah')->with('success', 'Mata kuliah berhasil diupdate.');
    }

    public function delete($kode)
    {
        $this->mataKuliahModel->delete($kode);
        return redirect()->to('/admin/matakuliah')->with('success', 'Mata kuliah berhasil dihapus.');
    }
}