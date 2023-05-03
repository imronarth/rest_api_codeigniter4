<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class PegawaiController extends ResourceController
{
    protected $modelName = 'App\Models\Pegawai';
    protected $format = 'json';
    protected $request;
    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    public function __construct()
    {
        $this->request = request();
    }
    public function index()
    {
        $data = [
            'message' => 'success',
            'data_pegawai' => $this->model->orderBy('id', 'DESC')->findAll(),
        ];
        return $this->respond($data, 200);
    }

    /**
     * Return the properties of a resource object
     *
     * @return mixed
     */
    public function show($id = null)
    {
        $data = [
            'message' => 'success',
            'pegawai_byId' => $this->model->find($id),
        ];
        if (!$data['pegawai_byId']) {
            return $this->failNotFound("data tidak ditemukan");
        }
        return $this->respond($data, 200);
    }

    /**
     * Create a new resource object, from "posted" parameters
     *
     * @return mixed
     */
    public function create()
    {
        $rules = $this->validate([
            'nama' => 'required',
            'jabatan' => 'required',
            'alamat' => 'required',
            'email' => 'required|valid_email|is_unique[pegawai.email]',
            'gambar' => 'uploaded[gambar]|max_size[gambar,2048]|mime_in[gambar,image/jpg,image/jpeg,image/png]',
        ]);
        if (!$rules) {
            $response = [
                "message" => $this->validator->getErrors(),
            ];

            return $this->failValidationErrors($response);
        }
        // upload
        $gambar = $this->request->getFile('gambar');
        $namaGambar = $gambar->getRandomName();
        $gambar->move('gambar', $namaGambar);

        $this->model->insert([
            'nama' => esc($this->request->getVar('nama')),
            'jabatan' => esc($this->request->getVar('jabatan')),
            'alamat' => esc($this->request->getVar('alamat')),
            'email' => esc($this->request->getVar('email')),
            'gambar' => $namaGambar,
        ]);
        $response = [
            'message' => 'Data pegawai berhasil ditambahkan'
        ];
        return $this->respondCreated($response);
    }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return mixed
     */
    public function update($id = null)
    {
        $pegawai = $this->model->find($id);
        if (!$pegawai) {
            return $this->failNotFound("data tidak ditemukan");
        }
        $rules = $this->validate([
            'nama' => 'required',
            'jabatan' => 'required',
            'alamat' => 'required',
            'email' => 'required|valid_email|is_unique[pegawai.email]',
            'gambar' => 'max_size[gambar,2048]|mime_in[gambar,image/jpg,image/jpeg,image/png]',
        ]);
        if (!$rules) {
            $response = [
                "message" => $this->validator->getErrors(),
            ];

            return $this->failValidationErrors($response);
        }
        $gambar = $this->request->getFile('gambar');
        if ($gambar) {
            if ($pegawai['gambar']) {
                unlink('gambar/' . $pegawai['gambar']);
            }
            // upload
            $namaGambar = $gambar->getRandomName();
            $gambar->move('gambar', $namaGambar);
        } else {
            $namaGambar = $pegawai['gambar'];
        }

        $this->model->update($id, [
            'nama' => esc($this->request->getVar('nama')),
            'jabatan' => esc($this->request->getVar('jabatan')),
            'alamat' => esc($this->request->getVar('alamat')),
            'email' => esc($this->request->getVar('email')),
            'gambar' => $namaGambar,
        ]);
        $response = [
            'message' => 'Data pegawai berhasil diubah'
        ];
        return $this->respond($response, 200);
    }

    /**
     * Delete the designated resource object from the model
     *
     * @return mixed
     */
    public function delete($id = null)
    {
        $pegawai = $this->model->find($id);
        if (!$pegawai) {
            return $this->failNotFound("data tidak ditemukan");
        }
        if ($pegawai['gambar']) {
            unlink('gambar/' . $pegawai['gambar']);
        }
        $this->model->delete($id);
        $response = [
            'message' => 'Data pegawai berhasil dihapus'
        ];
        return $this->respondDeleted($response);
    }
}
