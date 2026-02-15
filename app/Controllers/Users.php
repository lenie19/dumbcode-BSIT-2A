<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;
use App\Models\LogModel;

class Users extends Controller
{
    public function index(){
        $model = new UserModel();
        $data['users'] = $model->findAll();
        return view('users/index', $data);
    }

    public function save(){
        $name = $this->request->getPost('name');
        $ID = $this->request->getPost('ID');
        $password = $this->request->getPost('password');
        $address = $this->request->getPost('address');
        $bday = $this->request->getPost('bday');
        $role = $this->request->getPost('role');
        $status = $this->request->getPost('status');
        $phone = $this->request->getPost('phone');

        if (!$ID || !$password) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID and password are required']);
        }

        $userModel = new \App\Models\UserModel();
        $logModel = new LogModel();

        // Check if email already exists
        $existingUser = $userModel->where('ID', $ID)->first();
        if ($existingUser) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID is already in use']);
        }

        $data = [
            'name'       => $name,
            'ID'      => $ID,
            'address'   => $address,
            'bday'      => $bday,
            'password'   => password_hash($password, PASSWORD_DEFAULT),
            'role'       => $role,
            'status'     => $status,
            'phone'      => $phone,
            'updated_at' => date('Y-m-d H:i:s'),
            'deleted_at' => date('Y-m-d H:i:s')
        ];

        if ($userModel->insert($data)) {
            $logModel->addLog('New User has been added: ' . $name, 'ADD');
            return $this->response->setJSON(['status' => 'success']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to save user']);
        }
    }

    public function update(){
        $model = new UserModel();
        $logModel = new LogModel();
        $userId = $this->request->getPost('id');
        $name = $this->request->getPost('name');
        $ID = $this->request->getPost('ID');
        $password = $this->request->getPost('password');
        $address = $this->request->getPost('address');
        $bday = $this->request->getPost('bday');
        $role = $this->request->getPost('role');
        $status = $this->request->getPost('status');
        $phone = $this->request->getPost('phone');

    // Validate the input
        if (empty($ID)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID is required']);
        }

    // Check if email already exists for another user
        $existingUser = $model->where('ID', $ID)
        ->where('id !=', $userId)
        ->first();

        if ($existingUser) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID is already in use by another user.'
            ]);
        }

        $userData = [
            'name'       => $name,
            'ID'      => $ID,
            'address'   => $address,
            'bday'      => $bday,
            'role'       => $role,
            'status'     => $status,
            'phone'      => $phone,
            'updated_at' => date('Y-m-d H:i:s'),
            'deleted_at' => date('Y-m-d H:i:s')
        ];

        if (!empty($password)) {
            $userData['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $updated = $model->update($userId, $userData);

        if ($updated) {
            $logModel->addLog('New User has been apdated: ' . $name, 'UPDATED');
            return $this->response->setJSON([
                'success' => true,
                'message' => 'User updated successfully.'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error updating user.'
            ]);
        }
    }

    public function edit($id){
        $model = new UserModel();
    $user = $model->find($id); // Fetch user by ID

    if ($user) {
        return $this->response->setJSON(['data' => $user]); // Return user data as JSON
    } else {
        return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
    }
}

public function delete($id){
    $model = new UserModel();
    $logModel = new LogModel();
    $user = $model->find($id);
    if (!$user) {
        return $this->response->setJSON(['success' => false, 'message' => 'User not found.']);
    }

    $deleted = $model->delete($id);

    if ($deleted) {
        $logModel->addLog('Delete user', 'DELETED');
        return $this->response->setJSON(['success' => true, 'message' => 'User deleted successfully.']);
    } else {
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete user.']);
    }
}

public function fetchRecords()
{
    $request = service('request');
    $model = new \App\Models\UserModel();

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