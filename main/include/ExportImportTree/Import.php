<?php

if (isset($_FILES['importfile'])) {
    set_time_limit(0);
    $json = file_get_contents($_FILES['importfile']['tmp_name']);
    //echo "<pre>"; echo print_r($json,1); echo "</pre>";
    require 'custom/include/ExportImportTree/src/ImportTreeFromJson.php';
    (new ImportTreeFromJson)->import($json);
}

?><form method="POST"  enctype="multipart/form-data">
    <label for="importfile">Import file:</label>
        <input type="file" name="importfile" id="importfile" onchange="this.form.submit()" accept="application/json" />
        <button id="import">Import</button>
    <script>
        $('#import').click(function(){ $('#importfile').trigger('click'); });
    </script>
</form>
