<?php
/**
 * GUARD MANAGER - Hyper-Robust Edition
 */

class GuardManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function onboard_guard($data) {
        try {
            if (!$this->pdo->inTransaction()) {
                $this->pdo->beginTransaction();
            }

            $sql = "INSERT INTO guards (
                        guard_no, joining_date, full_name, father_name, caste, education, religion, 
                        cnic, dob, district, phone_number, blood_group, temporary_address, permanent_address,
                        is_ex_army, army_joining_date, army_discharge_date, army_discharge_reason,
                        govt_relative_name, govt_relative_designation, govt_relative_department,
                        previous_experience_ref, next_of_kin_name_address, next_of_kin_mobile,
                        base_salary, police_verification_ref_no
                    ) VALUES (
                        :guard_no, :joining_date, :full_name, :father_name, :caste, :education, :religion,
                        :cnic, :dob, :district, :phone_number, :blood_group, :temporary_address, :permanent_address,
                        :is_ex_army, :army_joining_date, :army_discharge_date, :army_discharge_reason,
                        :govt_relative_name, :govt_relative_designation, :govt_relative_department,
                        :previous_experience_ref, :next_of_kin_name_address, :next_of_kin_mobile,
                        :base_salary, :police_verification_ref_no
                    )";
            
            $stmt = $this->pdo->prepare($sql);
            
            $params = [
                ':guard_no' => $data['profile']['guard_no'] ?? ('G-' . uniqid()),
                ':joining_date' => $data['profile']['joining_date'] ?? date('Y-m-d'),
                ':full_name' => $data['profile']['full_name'] ?? 'UNKNOWN',
                ':father_name' => $data['profile']['father_name'] ?? 'UNKNOWN',
                ':caste' => $data['profile']['caste'] ?? '',
                ':education' => $data['profile']['education'] ?? '',
                ':religion' => $data['profile']['religion'] ?? 'Islam',
                ':cnic' => $data['profile']['cnic'] ?? uniqid(),
                ':dob' => $data['profile']['dob'] ?? '1990-01-01',
                ':district' => $data['profile']['district'] ?? '',
                ':phone_number' => $data['profile']['phone_number'] ?? '',
                ':blood_group' => $data['profile']['blood_group'] ?? 'O+',
                ':temporary_address' => $data['profile']['temporary_address'] ?? '',
                ':permanent_address' => $data['profile']['permanent_address'] ?? '',
                ':is_ex_army' => $data['profile']['is_ex_army'] ?? 0,
                ':army_joining_date' => (!empty($data['profile']['army_joining_date'])) ? $data['profile']['army_joining_date'] : null,
                ':army_discharge_date' => (!empty($data['profile']['army_discharge_date'])) ? $data['profile']['army_discharge_date'] : null,
                ':army_discharge_reason' => $data['profile']['army_discharge_reason'] ?? '',
                ':govt_relative_name' => $data['profile']['govt_relative_name'] ?? '',
                ':govt_relative_designation' => $data['profile']['govt_relative_designation'] ?? '',
                ':govt_relative_department' => $data['profile']['govt_relative_department'] ?? '',
                ':previous_experience_ref' => $data['profile']['previous_experience_ref'] ?? '',
                ':next_of_kin_name_address' => $data['profile']['next_of_kin_name_address'] ?? '',
                ':next_of_kin_mobile' => $data['profile']['next_of_kin_mobile'] ?? '',
                ':base_salary' => $data['profile']['base_salary'] ?? 0,
                ':police_verification_ref_no' => $data['profile']['police_verification_ref_no'] ?? ''
            ];

            $stmt->execute($params);
            $guard_id = $this->pdo->lastInsertId();

            if (!empty($data['witnesses']) && is_array($data['witnesses'])) {
                $sql_w = "INSERT INTO guard_witnesses (guard_id, witness_name, witness_phone, witness_address) VALUES (?, ?, ?, ?)";
                $stmt_w = $this->pdo->prepare($sql_w);
                foreach ($data['witnesses'] as $w) {
                    if (!empty($w['name'])) {
                        $stmt_w->execute([$guard_id, $w['name'], $w['phone'] ?? '', $w['address'] ?? '']);
                    }
                }
            }

            if (!empty($data['kit']) && is_array($data['kit'])) {
                $sql_k = "INSERT INTO guard_initial_kit (guard_id, shirt_trousers, cap, belt, boots, jersey) VALUES (?, ?, ?, ?, ?, ?)";
                $this->pdo->prepare($sql_k)->execute([
                    $guard_id,
                    $data['kit']['shirt_trousers'] ?? 0,
                    $data['kit']['cap'] ?? 0,
                    $data['kit']['belt'] ?? 0,
                    $data['kit']['boots'] ?? 0,
                    $data['kit']['jersey'] ?? 0
                ]);
            }

            $this->pdo->commit();
            return ['status' => 'success', 'guard_id' => $guard_id];
        } catch (Exception $e) {
            if($this->pdo->inTransaction()) $this->pdo->rollBack();
            file_put_contents('error_log.txt', "ONBOARD_ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function get_active_guards() {
        return $this->pdo->query("SELECT * FROM guards WHERE is_deleted = 0 ORDER BY joining_date DESC")->fetchAll();
    }

    public function save_performance_audit($data) {
        try {
            $sql = "INSERT INTO performance_audits 
                    (guard_id, billing_month, total_present_days, double_shifts, lost_id_card_fines, shift_misconduct_fines, custom_client_penalties, audit_notes) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['guard_id'],
                date('F Y'),
                $data['total_present_days'] ?? 0,
                $data['double_shifts_count'] ?? 0,
                ($data['lost_id_card_incidents'] ?? 0) * 1000,
                ($data['shift_misconduct_incidents'] ?? 0) * 500,
                $data['custom_client_penalties'] ?? 0,
                $data['performance_notes'] ?? ''
            ]);
            return ['status' => 'success'];
        } catch (Exception $e) {
            file_put_contents('error_log.txt', "AUDIT_ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function get_audit_history() {
        $sql = "SELECT pa.*, g.full_name 
                FROM performance_audits pa 
                JOIN guards g ON pa.guard_id = g.guard_id 
                ORDER BY pa.created_at DESC LIMIT 20";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function get_payroll_data($month) {
        $sql = "SELECT g.guard_id, g.full_name, g.base_salary, 
                       pa.lost_id_card_fines, pa.shift_misconduct_fines, pa.custom_client_penalties
                FROM guards g
                LEFT JOIN performance_audits pa ON g.guard_id = pa.guard_id AND pa.billing_month = ?
                WHERE g.is_deleted = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$month]);
        return $stmt->fetchAll();
    }

    public function release_payroll($guard_id, $month) {
        try {
            $stmt = $this->pdo->prepare("SELECT g.base_salary, pa.* FROM guards g LEFT JOIN performance_audits pa ON g.guard_id = pa.guard_id AND pa.billing_month = ? WHERE g.guard_id = ?");
            $stmt->execute([$month, $guard_id]);
            $res = $stmt->fetch();
            if(!$res) throw new Exception("DATA_NOT_FOUND");
            $base = $res['base_salary'];
            $deductions = 500 + ($res['lost_id_card_fines'] ?? 0) + ($res['shift_misconduct_fines'] ?? 0) + ($res['custom_client_penalties'] ?? 0);
            $net = $base - $deductions;
            $sql = "INSERT INTO payroll_logs (guard_id, billing_month, base_salary, total_deductions, net_pay, payment_status, released_at) VALUES (?, ?, ?, ?, ?, 'released', CURRENT_TIMESTAMP)";
            $this->pdo->prepare($sql)->execute([$guard_id, $month, $base, $deductions, $net]);
            return ['status' => 'success'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
