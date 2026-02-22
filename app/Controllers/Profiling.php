<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ProfilingModel;
use CodeIgniter\Controller;
use App\Models\LogModel;

class Profiling extends Controller
{
public function delete($id){
    $model = new ProfilingModel();
    $logModel = new LogModel();
    $user = $model->find($id);
    if (!$user) {
        return $this->response->setJSON(['success' => false, 'message' => 'Profiling not found.']);
    }

    $deleted = $model->delete($id);

    if ($deleted) {
        $logModel->addLog('Delete Profiling', 'DELETED');
        return $this->response->setJSON(['success' => true, 'message' => 'Profiling deleted successfully.']);
    } else {
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete Profiling.']);
    }
}
