<?php 
$db = new SQLite3('../data/XLN_new_DBA.db');

// Get case data
$caseResult = null;
if(isset($_GET['caseID'])) {
    $sql = "SELECT * FROM cases WHERE caseID=:cid";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':cid', $_GET['uid'], SQLITE3_TEXT);
    $result = $stmt->execute();
    
    while($row = $result->fetchArray(SQLITE3_NUM)){
        $caseResult = $row;
    }
}

if (isset($_POST['submit'])){
    $db = new SQLite3('../data/XLN_new_DBA.db');
    
    // Update only description and status
    $sql = "UPDATE Cases SET Status = :status, Description = :desc WHERE caseID = :cid";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':cid', $_GET['caseID'], SQLITE3_TEXT);
    $stmt->bindParam(':status', $_POST['status'], SQLITE3_TEXT);
    $stmt->bindParam(':desc', $_POST['description'], SQLITE3_TEXT);
    $stmt->execute();
    
    header('Location: ViewAllCases.php');
}
?>
<div class="container bgColor">
    <main role="main" class="pb-3">
        <div class="row">
            <div class="col-11">
                <form method="post">
                    <h3>Update Case</h3>
                    <div class="form-group col-md-3">
                        <label class="control-label labelFont">Case ID</label>
                        <input class="form-control" type="text" readonly value="<?php echo $_GET['cid']; ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="control-label labelFont">Status</label>
                        <select class="form-control" name="status">
                            <option value="0" <?php echo ($caseResult[3] == 0) ? 'selected' : ''; ?>>Closed</option>
                            <option value="1" <?php echo ($caseResult[3] == 1) ? 'selected' : ''; ?>>Open</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="control-label labelFont">Description</label>
                        <textarea class="form-control" name="description" rows="4"><?php echo $caseResult[4]; ?></textarea>
                    </div>
                    
                    <div class="form-group col-md-3 mt-3">
                        <input type="submit" name="submit" value="Update" class="btn btn-primary">
                    </div>
                    <div class="form-group col-md-3 mt-3">
                        <a href="viewCases.php" class="btn btn-secondary">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>