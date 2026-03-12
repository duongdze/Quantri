<?php
class GuideWorkModel
{
    private static function ensurePdo()
    {
        if (BaseModel::getPdo() === null) {
            new BaseModel();
        }
        return BaseModel::getPdo();
    }

    public static function getGuideByUserId($userId)
    {
        $pdo = self::ensurePdo();
        $sql = "SELECT * FROM guides WHERE user_id = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function getAllGuides()
    {
        $pdo = self::ensurePdo();
        $sql = "SELECT G.*, U.full_name, U.email, U.phone
                FROM guides G
                JOIN users U ON G.user_id = U.user_id
                WHERE U.role = 'guide'
                ORDER BY U.full_name ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAssignmentsByGuideId($guideId)
    {
        $pdo = self::ensurePdo();
        $sql = "SELECT TA.guide_id, TA.*, T.name as tour_name, T.description, T.id as tour_id
            FROM tour_assignments TA
            JOIN tours T ON TA.tour_id = T.id
            WHERE TA.guide_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$guideId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTourById($tourId)
    {
        $pdo = self::ensurePdo();
        $sql = "SELECT T.*, TC.name as category_name
                FROM tours T
                LEFT JOIN tour_categories TC ON T.category_id = TC.id
                WHERE T.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tourId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function getItinerariesByTourId($tourId)
    {
        $pdo = self::ensurePdo();
        $sql = "SELECT * FROM itineraries WHERE tour_id = ? ORDER BY day_number ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tourId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAssignment($tourId, $guideId)
    {
        $pdo = self::ensurePdo();
        $sql = "SELECT * FROM tour_assignments WHERE tour_id = ? AND guide_id = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tourId, $guideId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function getAssignmentById($assignmentId)
    {
        $pdo = self::ensurePdo();
        $sql = "SELECT * FROM tour_assignments WHERE id = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$assignmentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function deleteAssignment($assignmentId)
    {
        $pdo = self::ensurePdo();
        $sql = "DELETE FROM tour_assignments WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$assignmentId]);
    }

    public static function updateAssignmentStatus($assignmentId, $status)
    {
        $pdo = self::ensurePdo();
        $sql = "UPDATE tour_assignments SET status = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$status, $assignmentId]);
    }
}
