<?php

namespace App\Controllers;

use App\Models\ParentModel;
use App\Models\LogModel;
use CodeIgniter\Controller;

class Parents extends Controller
{
    public function index()
    {
        $model = new ParentModel();
        $data['parents'] = $model->findAll();
        return view('parents/index', $data);
    }

    public function save()
    {
        $name = $this->request->getPost('name');
        $gender = $this->request->getPost('gender');
        $address = $this->request->getPost('address');

        $model = new ParentModel();
        $logModel = new LogModel();

        $data = [
            'name' => $name,
            'gender' => $gender,
            'address' => $address,
        ];

        if ($model->insert($data)) {
            $logModel->addLog('New Parent has been added: ' . $name, 'ADD');
            return $this->response->setJSON(['status' => 'success']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to save parent']);
        }
    }

    public function update()
    {
        $model = new ParentModel();
        $logModel = new LogModel();
        $id = $this->request->getPost('id');
        $name = $this->request->getPost('name');
        $gender = $this->request->getPost('gender');
        $address = $this->request->getPost('address');

        $data = [
            'name' => $name,
            'gender' => $gender,
            'address' => $address,
        ];

        $updated = $model->update($id, $data);

        if ($updated) {
            $logModel->addLog('Parent updated: ' . $name, 'UPDATED');
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Parent updated successfully.'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error updating parent.'
            ]);
        }
    }

    public function edit($id)
    {
        $model = new ParentModel();
        $item = $model->find($id);

        if ($item) {
            return $this->response->setJSON(['data' => $item]);
        } else {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Parent not found']);
        }
    }

    public function delete($id)
    {
        $model = new ParentModel();
        $logModel = new LogModel();
        $item = $model->find($id);
        if (!$item) {
            return $this->response->setJSON(['success' => false, 'message' => 'Parent not found.']);
        }

        $deleted = $model->delete($id);

        if ($deleted) {
            $logModel->addLog('Deleted parent', 'DELETED');
            return $this->response->setJSON(['success' => true, 'message' => 'Parent deleted successfully.']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete parent.']);
        }
    }

    public function fetchRecords()
    {
        $request = service('request');
        $model = new ParentModel();

        $start = $request->getPost('start') ?? 0;
        $length = $request->getPost('length') ?? 10;
        $searchValue = $request->getPost('search')['value'] ?? '';

        $totalRecords = $model->countAll();
        $result = $model->getRecords($start, $length, $searchValue);

        $data = [];
        $counter = $start + 1;
        foreach ($result['data'] as $row) {
            $row['row_number'] = $counter++;
            $data[] = $row;
        }

        return $this->response->setJSON([
            'draw' => intval($request->getPost('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $result['filtered'],
            'data' => $data,
        ]);
    }
}
