class AnalyticsTracker {
    // ... existing code ...
    
    public function getCurrentStreak() {
        $streak = 0;
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        // Get user's reading history ordered by date
        $query = "SELECT DISTINCT DATE(timestamp) as read_date 
                 FROM reading_history 
                 WHERE user_id = ? 
                 ORDER BY read_date DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$this->userId]);
        $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($dates)) {
            return 0;
        }
        
        // Check if user read today or yesterday to maintain streak
        $lastRead = $dates[0];
        if ($lastRead != $today && $lastRead != $yesterday) {
            return 0;
        }
        
        // Count consecutive days
        $previousDate = null;
        foreach ($dates as $date) {
            if ($previousDate === null) {
                $streak++;
                $previousDate = $date;
                continue;
            }
            
            $expectedPrevious = date('Y-m-d', strtotime($date . ' +1 day'));
            if ($expectedPrevious != $previousDate) {
                break;
            }
            
            $streak++;
            $previousDate = $date;
        }
        
        return $streak;
    }
    
    // ... existing code ...
}