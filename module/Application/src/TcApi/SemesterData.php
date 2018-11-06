<?php

namespace Application\TcApi;

use Application\Service\PdoDb;

class SemesterData extends TcApi
{
    /**
     * @var \PDO
     */
    protected $db = null;

    public function __construct($sm)
    {
        parent::__construct($sm);
        $this->method = "GET";
        $this->apiName = 'semester-data';

       $this->db = $sm->get(PdoDb::class);


    }


    /**
     * 產生隨機密碼
     * @param int $length
     * @return string
     */
    protected function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


    /**
     * 同步 API 資料
     * @return mixed
     */
    public function syncData()
    {

        $objData = $this->getData();
//        echo  '<Pre>';
//        print_r($objData);
//echo $objData->更新時間;

        // 檢查是否更新
        $sql = "SELECT * FROM sync_log WHERE data_update_time=:update_time";
        $statement = $this->db->prepare($sql);

        $statement->execute([
            'update_time' => $objData->更新時間
        ]);

        $syncLogRes = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!$syncLogRes) {
            // 先判斷本學期資料是否存在
            $sql = "SELECT * FROM semester WHERE year=:year AND semester=:semester";
            $statement = $this->db->prepare($sql);
            $statement->execute([
                'year' => $objData->學年,
                'semester' => $objData->學期,
            ]);
            $semesterRes = $statement->fetch(\PDO::FETCH_ASSOC);

            // 如果沒有學期資料
            if (!$semesterRes) {
                $sql = "INSERT INTO semester (year,semester,begin_date,end_date,update_time,semester_start_date,semester_end_date) VALUES (:year,:semester,:begin_date,:end_date,:update_time,:semester_start_date,:semester_end_date)";
                $statement = $this->db->prepare($sql);
                $statement->execute([
                    'year' => $objData->學年,
                    'semester' => $objData->學期,
                    'begin_date' => $objData->學期開始日期,
                    'end_date' => $objData->學期結束日期,
                    'update_time' => $objData->更新時間,
                    'semester_start_date' => $objData->開學日,
                    'semester_end_date' => $objData->結業日,
                ]);
                $lastId = $this->db->lastInsertId();

                $sql = "SELECT * FROM semester WHERE id=" . $lastId;
                $semesterRes = $this->db->query($sql)->fetch(\PDO::FETCH_ASSOC);
            }

            // 學期編班
            foreach ($objData->學期編班 as $val) {
                // 先檢查已有資料
                $sql = "SELECT * FROM semester_class 
                        WHERE semester_id=:semester_id AND
                        grade=:grade AND 
                        class_no=:class_no";
                $statement = $this->db->prepare($sql);
                $statement->execute([
                    'semester_id' => $semesterRes['id'],
                    'grade' => $val->年級,
                    'class_no' => $val->班序
                ]);

                $classRes = $statement->fetch(\PDO::FETCH_ASSOC);

                // 如果沒有班級資料
                if (!$classRes) {
                    $sql = "INSERT INTO `semester_class` (`grade`, `class_name`, `class_no`, `semester_id`, `tutor`) VALUES (:grade, :class_name, :class_no, :semester_id, :tutor)";

                    // 導師
                    $tempArr = [];
                    foreach ($val->導師 as $val2)
                        $tempArr[] = $val2->姓名;

                    $statement = $this->db->prepare($sql);
                    $statement->execute([
                        'grade' => $val->年級,
                        'class_name' => $val->班名,
                        'class_no' => $val->班序,
                        'semester_id' => $semesterRes['id'],
                        'tutor' => implode(',', $tempArr)
                    ]);

                    $lastId = $this->db->lastInsertId();

                    $sql = "SELECT * FROM semester_class WHERE id=" . $lastId;
                    $classRes = $this->db->query($sql)->fetch(\PDO::FETCH_ASSOC);

                }

                // 學生學期編班
                foreach ($val->學期編班 as $student) {

                    // 先檢查已有學生
                    $sql = "SELECT * FROM student WHERE edu_key=:edu_key";
                    $statement = $this->db->prepare($sql);
                    $statement->execute(
                        [
                            'edu_key' => $student->身分證編碼
                        ]
                    );
                    $studentRes = $statement->fetch(\PDO::FETCH_ASSOC);

                    // 如果沒有學生
                    if (!$studentRes) {

                        // 先新增一筆 user 帳號
                        $sql = "INSERT INTO `user` ( `username`, `name`, `password`,`role`) VALUES (:username, :name, :password, :role);";
                        $statement = $this->db->prepare($sql);
                        $statement->execute(
                            [
                                'username' => $student->身分證編碼,
                                'name' => $student->姓名,
                                'password' => $this->generateRandomString(32),
                                'role' => 'student',
                            ]
                        );
                        $userId = $this->db->lastInsertId();


                        $sql = "INSERT INTO `student` (`name`, `edu_key`, `stud_no`, `sex`, `update_time`, `user_id`) VALUES ( :name, :edu_key, :stud_no, :sex, :update_time, :user_id)";
                        $statement = $this->db->prepare($sql);
                        $statement->execute(
                            [
                                'name' => $student->姓名,
                                'edu_key' => $student->身分證編碼,
                                'stud_no' => $student->學號,
                                'sex' => $student->性別,
                                'update_time' => $objData->更新時間,
                                'user_id' => $userId
                            ]
                        );
                        $studentId = $this->db->lastInsertId();

                        $sql = "SELECT * FROM student WHERE id=" . $studentId;
                        $studentRes = $this->db->query($sql)->fetch(\PDO::FETCH_ASSOC);

                    }

                    // 學期編班

                    $sql = "SELECT * FROM semester_student WHERE semester_class_id=:class_id AND student_id=:student_id";

                    $statement = $this->db->prepare($sql);
                    $statement->execute(
                        [
                            'class_id' => $classRes['id'],
                            'student_id' => $studentRes['id']
                        ]
                    );

                    $semesterStudentRes = $statement->fetch(\PDO::FETCH_ASSOC);

                    if (!$semesterStudentRes) {
                        $sql = "INSERT INTO `semester_student` (`student_id`, `number`, `semester_class_id`) VALUES (:student_id, :number, :semester_class_id);";

                        $statement = $this->db->prepare($sql);
                        $statement->execute(
                            [
                                'semester_class_id' => $classRes['id'],
                                'student_id' => $studentRes['id'],
                                'number' => $student->座號
                            ]
                        );
                    } // 如果有資料，更改座號
                    else {
                        $sql = "UPDATE `semester_student` SET number=:number WHERE id=:id";
                        $statement = $this->db->prepare($sql);
                        $statement->execute(
                            [
                                'id' => $semesterStudentRes['id'],
                                'number' => $student->座號
                            ]
                        );
                    }

                }

            }


            //========================
            // 單位資料

            foreach ($objData->單位資料 as $val) {
                // 檢查已有資料
                $sql = "SELECT * FROM school_office WHERE name=:name";

                $statement = $this->db->prepare($sql);
                $statement->execute(
                    [
                        'name' => $val->處室名稱
                    ]
                );
                $schoolOfficeRes = $statement->fetch(\PDO::FETCH_ASSOC);

                if (!$schoolOfficeRes) {
                    $sql = "INSERT INTO `school_office` ( `name`, `rank`, `office_phone`, `office_phone_ext`, `office_url`) VALUES (:name, :rank, :office_phone, :office_phone_ext, :office_url);";

                    $statement = $this->db->prepare($sql);
                    $statement->execute(
                        [
                            'name' => $val->處室名稱,
                            'rank' => $val->排序,
                            'office_phone' => $val->電話,
                            'office_phone_ext' => $val->分機,
                            'office_url' => $val->網址
                        ]
                    );

                    $lastId = $this->db->lastInsertId();

                    $sql = "SELECT * FROM school_office WHERE id=" . $lastId;
                    $schoolOfficeRes = $this->db->query($sql)->fetch(\PDO::FETCH_ASSOC);

                }

                if ($val->單位職稱) {
                    foreach ($val->單位職稱 as $title) {
                        // 檢查已有資料
                        $sql = "SELECT * FROM teacher_title WHERE name=:name and school_office_id=:school_office_id";

                        $statement = $this->db->prepare($sql);
                        $statement->execute(
                            [
                                'name' => $title->職稱,
                                'school_office_id' => $schoolOfficeRes['id']
                            ]
                        );
                        $teacherTitleRes = $statement->fetch(\PDO::FETCH_ASSOC);

                        if (!$teacherTitleRes) {
                            $sql = "INSERT INTO `teacher_title` (`name`, `school_office_id`, `title_kind`, `rank`) VALUES (:name, :school_office_id, :title_kind, :rank);";

                            $statement = $this->db->prepare($sql);
                            $statement->execute(
                                [
                                    'name' => $title->職稱,
                                    'school_office_id' => $schoolOfficeRes['id'],
                                    'title_kind' => $title->職別,
                                    'rank' => $title->排序,
                                ]
                            );
//
                        }


                    }
                }

            }



            $teacherTitleArray = [];
            // 列出職稱
            $sql = "SELECT * FROM teacher_title";
            $arr = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($arr as $val2)
                $teacherTitleArray[$val2['name']] = $val2['id'];

            // 本學期班級代碼
            $semesterClassArray = [];
            $sql = "SELECT * FROM semester_class";
            $arr = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($arr as $val2)
                $semesterClassArray[$val2['grade']][$val2['class_no']] = $val2['id'];




            // 學期教職員
            if (isset($objData->學期教職員)) {
                foreach ($objData->學期教職員 as $teacher) {
                    // 先檢查教師是否存在
                    $sql = "SELECT * FROM teacher WHERE edu_key=:edu_key";

                    $statement = $this->db->prepare($sql);
                    $statement->execute(
                        [
                            'edu_key' => $teacher->身分證編碼,
                        ]
                    );
                    $teacherRes = $statement->fetch(\PDO::FETCH_ASSOC);

                    if (!$teacherRes) {
                        // 先新增一筆 user 帳號
                        $sql = "INSERT INTO `user` ( `username`, `name`, `password`,`role`) VALUES (:username, :name, :password, :role);";
                        $statement = $this->db->prepare($sql);
                        $statement->execute(
                            [
                                'username' => $teacher->身分證編碼,
                                'name' => $teacher->姓名,
                                'password' => $this->generateRandomString(32),
                                'role' => 'teacher',
                            ]
                        );
                        $userId = $this->db->lastInsertId();


                        $sql = "INSERT INTO `teacher` (`name`, `edu_key`, `sex`, `update_time`, `user_id`) VALUES ( :name, :edu_key, :sex, :update_time, :user_id)";
                        $statement = $this->db->prepare($sql);
                        $statement->execute(
                            [
                                'name' => $teacher->姓名,
                                'edu_key' => $teacher->身分證編碼,
                                'sex' => $teacher->性別,
                                'update_time' => $objData->更新時間,
                                'user_id' => $userId
                            ]
                        );
                        $teacherId = $this->db->lastInsertId();

                        $sql = "SELECT * FROM student WHERE id=" . $teacherId;
                        $teacherRes = $this->db->query($sql)->fetch(\PDO::FETCH_ASSOC);
                    }

                    // 學期任職

                    $sql = "SELECT * FROM semester_teacher WHERE teacher_id=:teacher_id AND teacher_title_id=:teacher_title_id AND semester_id=:semester_id";

                    $statement = $this->db->prepare($sql);
                    $statement->execute(
                        [
                            'teacher_id' => $teacherRes['id'],
                            'teacher_title_id' => $teacherTitleArray[$teacher->職稱],
                            'semester_id' => $semesterRes['id']
                        ]
                    );
                    $semesterTeacherRes = $statement->fetch(\PDO::FETCH_ASSOC);

                    if (!$semesterTeacherRes) {
                        $sql = "INSERT INTO `semester_teacher` (`semester_id`, `teacher_id`, `teacher_title_id`) VALUES (:semester_id, :teacher_id, :teacher_title_id);";
                        $statement = $this->db->prepare($sql);
                        $statement->execute(
                            [
                                'teacher_id' => $teacherRes['id'],
                                'teacher_title_id' => $teacherTitleArray[$teacher->職稱],
                                'semester_id' => $semesterRes['id']
                            ]
                        );
                        $lastId = $this->db->lastInsertId();

                        $sql = "SELECT * FROM semester_teacher WHERE id=" . $lastId;
                        $semesterTeacherRes = $this->db->query($sql)->fetch(\PDO::FETCH_ASSOC);

                    }

                    // 任課
                    if ($teacher->任教科目) {
                        foreach ($teacher->任教科目 as $course) {
                            $semester_class_id = $semesterClassArray[$course->年級][$course->班序];
                            $sql = "SELECT * FROM teacher_course WHERE semester_teacher_id=:semester_teacher_id AND semester_class_id=:semester_class_id AND subject_name=:subject_name";

                            $statement = $this->db->prepare($sql);
                            $statement->execute(
                                [
                                    'semester_teacher_id' => $semesterTeacherRes['id'],
                                    'semester_class_id' => $semester_class_id,
                                    'subject_name' => $course->科目
                                ]
                            );
                            $teacherCourseRes = $statement->fetch(\PDO::FETCH_ASSOC);

                            if (!$teacherCourseRes) {
                                $sql = "INSERT INTO `teacher_course` (`subject_name`, `section_number`, `semester_teacher_id`, `semester_class_id`) VALUES (:subject_name, :section_number, :semester_teacher_id, :semester_class_id)";
                                $statement = $this->db->prepare($sql);
                                $statement->execute(
                                    [
                                        'subject_name' => $course->科目,
                                        'section_number' => $course->時數,
                                        'semester_teacher_id' => $semesterTeacherRes['id'],
                                        'semester_class_id' => $semester_class_id
                                    ]
                                );
                            }

                            // 班級任課
                            $sql = "SELECT * FROM semester_class_has_teacher WHERE semester_class_id=:semester_class_id AND teacher_id=:teacher_id ";
                            $statement = $this->db->prepare($sql);
                            $statement->execute(
                                [
                                    'semester_class_id' => $semester_class_id,
                                    'teacher_id' => $teacherRes['id'],
                                ]
                            );
                            $semesterClassHasTeacherRes = $statement->fetch(\PDO::FETCH_ASSOC);

                            if (!$semesterClassHasTeacherRes) {
                                $sql = "INSERT INTO `semester_class_has_teacher` (`semester_class_id`, `teacher_id`) VALUES (:semester_class_id, :teacher_id)";
                                $statement = $this->db->prepare($sql);
                                $statement->execute(
                                    [
                                        'semester_class_id' => $semester_class_id,
                                        'teacher_id' => $teacherRes['id'],
                                    ]
                                );
                            }

                        }
                    }

                }
            }

            // 更新 log
            $sql = "INSERT INTO `sync_log` ( `sync_state`, `data_update_time`, `update_time`) VALUES (1,:data_update_time,:update_time);";
            $statement = $this->db->prepare($sql);

            $statement->execute([
                'data_update_time' => $objData->更新時間,
                'update_time' => date("Y-m-d H:i:s"),
            ]);

            $lastId = $this->db->lastInsertId();
            $sql = "SELECT * FROM sync_log WHERE id=".$lastId;;

            $syncLogRes = $this->db->query($sql)->fetch(\PDO::FETCH_ASSOC);
        }

        return $syncLogRes;

    }

}
