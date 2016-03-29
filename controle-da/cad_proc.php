<?php
//Conecta no Banco de Dados
require_once('../../../Connections/conex.php');
include("../../classes/teste.php");

$temp = new testesql();
//Inicia a seção
if (!isset($_SESSION)) {
    session_start();
}

// ** Logout the current user. **
$logoutAction = $_SERVER['PHP_SELF'] . "?doLogout=true";
if ((isset($_SERVER['QUERY_STRING'])) && ($_SERVER['QUERY_STRING'] != "")) {
    $logoutAction .="&" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    //to fully log out a visitor we need to clear the session varialbles
    $_SESSION['MM_Username'] = NULL;
    $_SESSION['MM_UserGroup'] = NULL;
    $_SESSION['MM_UserCod'] = NULL;
    $_SESSION['PrevUrl'] = NULL;
    unset($_SESSION['MM_Username']);
    unset($_SESSION['MM_UserGroup']);
    unset($_SESSION['MM_UserCod']);
    unset($_SESSION['PrevUrl']);

    $logoutGoTo = "../../index.php";
    if ($logoutGoTo) {
        header("Location: $logoutGoTo");
        exit;
    }
}

$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

$MM_restrictGoTo = "../../acesso_negado.php";
if (!((isset($_SESSION['MM_Username'])) && ($temp->isAuthorized("", $MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {
    $MM_qsChar = "?";
    $MM_referrer = $_SERVER['PHP_SELF'];
    if (strpos($MM_restrictGoTo, "?"))
        $MM_qsChar = "&";
    if (isset($QUERY_STRING) && strlen($QUERY_STRING) > 0)
        $MM_referrer .= "?" . $QUERY_STRING;
    $MM_restrictGoTo = $MM_restrictGoTo . $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
    header("Location: " . $MM_restrictGoTo);
    exit;
}

if (IsSet($_SESSION["MM_Username"])) {
    $loginUsername = $_SESSION["MM_Username"];
    $loginUserGroup = $_SESSION["MM_UserGroup"];
    $loginUserCod = $_SESSION["MM_UserCod"];
}
$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
//Pegando os numeros que não se repetem
$numProc = $_POST['Num_Proc'];
$numOfi = $_POST['NumOfi_Proc'];
$numMr = $_POST['NumMr_Proc'];
//Consulta para verificar cadastro duplicado
mysql_select_db($database_conex, $conex);
$query_RsTest = "SELECT * FROM tabprocesso WHERE Num_Proc='$numProc' OR NumMr_Proc='$numMr' OR NumOfi_Proc='$numOfi'";
$RsTest = mysql_query($query_RsTest, $conex) or die(mysql_error());
$row_RsTest = mysql_fetch_assoc($RsTest);
$totalRows_RsTest = mysql_num_rows($RsTest);

//Verifica se pode ser cadastrado
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2")) {
    if ($totalRows_RsTest == 0) {
         $insertSQL = sprintf("INSERT INTO tabprocesso (DataEnt_Proc, Ori_Proc, Num_Proc, NumMr_Proc, NumOfi_Proc, Desc_Proc, Local_Proc, Resp_Proc, Solu_Proc, DataSai_Proc, Dest_Proc, Obs_Proc, DataCad_Proc, UserCad_Proc) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
            $temp->Converter_Dia($_POST['DataEnt_Proc']),
            $temp->GetSQLValueString($_POST['Ori_Proc'], "text"), 
            $temp->GetSQLValueString($_POST['Num_Proc'], "text"), 
            $temp->GetSQLValueString($_POST['NumMr_Proc'], "text"), 
            $temp->GetSQLValueString($_POST['NumOfi_Proc'], "text"), 
            $temp->GetSQLValueString($_POST['Desc_Proc'], "text"), 
            $temp->GetSQLValueString($_POST['Local_Proc'], "text"),
            $temp->GetSQLValueString($_POST['Resp_Proc'], "text"), 
            $temp->GetSQLValueString($_POST['Solu_Proc'], "text"), 
            $temp->Converter_Dia($_POST['DataSai_Proc']), 
            $temp->GetSQLValueString($_POST['Dest_Proc'], "text"), 
            $temp->GetSQLValueString($_POST['Obs_Proc'], "text"), 
            $temp->Converter_Dia(date("d/m/Y")), 
            $temp->GetSQLValueString($loginUserCod, "text"));
         mysql_select_db($database_conex, $conex);
        $Result1 = mysql_query($insertSQL, $conex) or die(mysql_error());

//Fim
        $insertGoTo = "../conf/conf_proc.php";
    }
    if (isset($_SERVER['QUERY_STRING'])) {
        $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
        $insertGoTo .= $_SERVER['QUERY_STRING'];
    }
    header(sprintf("Location: %s", $insertGoTo));
}

mysql_select_db($database_conex, $conex);
$query_RsProj = "SELECT * FROM tabsetor ORDER BY Nome_Set";
$RsProj = mysql_query($query_RsProj, $conex) or die(mysql_error());
$row_RsProj = mysql_fetch_assoc($RsProj);
$totalRows_RsProj = mysql_num_rows($RsProj);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <title><?php echo $temp->title(); ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <script src="../../../SpryAssets/SpryMenuBar.js" type="text/javascript"></script>
        <link href="../../../SpryAssets/SpryMenuBarHorizontal.css" rel="stylesheet" type="text/css">
        <link href="../../../SpryAssets/SpryMenuBarVertical.css" rel="stylesheet" type="text/css">
        <script src="../../classes/funcoes.js" type="text/javascript"></script>
        <script src="../../../../SpryAssets/SpryValidationSelect.js" type="text/javascript"></script>
        <script> setTimeout('MenuBars()', 100);</script>
        <link href="../../../../SpryAssets/SpryValidationSelect.css" rel="stylesheet" type="text/css">
    </head>
    <link href="../../../css/teste.css" rel="stylesheet">
    <link href="../../../css/style.css" rel="stylesheet">
    <link href="../../../css/bootstrap/css/bootstrap.css" rel="stylesheet">
    <style>

        label{
            display:block;
        }
    </style>

    <body class="jumbotron">
        <div class="navbar-fixed-top">
<?php echo $temp->menus($loginUserGroup, $logoutAction); ?>
        </div>
        <div class="container">
            <br><br><br><br><br><br>
            <legend>CADASTRO DE PROCESSO</legend>
            <form action="<?php echo $editFormAction; ?>" method="POST" name="form2" class="form-inline" role="form" autocomplete="on">
                <div class="form-group">
                    <label for="text">Nº Processo</label>
                    <input name="Num_Proc" type="text"  class="form-control" id="Num_Proc"  size="18" maxlength="17"  onKeyPress="Proc(event, this)">
                </div>&nbsp;&nbsp;
                <div class="form-group">
                    <label for="text">Nº MR</label>
                    <input name="NumMr_Proc" type="text"  class="form-control" id="NumMr_Proc"  size="10" maxlength="8"  onKeyPress="Mr(event, this)">
                </div>&nbsp;&nbsp;
                <div class="form-group">
                    <label for="text">Nº MR</label>
                    <input name="NumOfi_Proc" type="text"  class="form-control" id="NumOfi_Proc"  size="15" maxlength="30">
                </div>            
                <br><br>
                <div class="form-group">
                    <label for="text">Data Entrada</label>
                    <input id="DataEnt_Proc" name="DataEnt_Proc" type="text" class="form-control" size="10" maxlength="10" onKeyPress="DataHora(event, this)" required autofocus>
                </div>&nbsp;&nbsp;
                <div class="form-group">
                    <label for="text">Origem</label>
                    <span id="spryselect1">
                        <select id="Ori_Proc" name="Ori_Proc" class="form-control">
                            <option value="-1">SELECIONE UM TIPO</option>
<?php
do {
    echo "<option value='" . $row_RsProj['Cod_Set'] . "'>" . $row_RsProj['Nome_Set'] . "</option>";
} while ($row_RsProj = mysql_fetch_assoc($RsProj));
mysql_data_seek($RsProj, 0)
?>
                        </select>
                        <span class="selectInvalidMsg">Selecione um item válido.</span></span></div>
                <br><br>
                <div class="form-group">
                    <label for="text">Assunto</label>
                    <input id="Desc_Proc" name="Desc_Proc" type="text" class="form-control" size="40" maxlength="255" required autofocus>
                </div>&nbsp;&nbsp;
                <div class="form-group">
                    <label for="text">Local</label>
                    <input id="Local_Proc" name="Local_Proc" type="text" class="form-control" size="10" maxlength="10">
                </div>&nbsp;&nbsp;
                <div class="form-group">
                    <label for="text">Responsável</label>
                    <input id="Resp_Proc" name="Resp_Proc" type="text" class="form-control" size="17" maxlength="50" required autofocus>
                </div>
                <br><br>
                <div class="form-group">
                    <label for="text">Solução</label>
                    <input id="Solu_Proc" name="Solu_Proc" type="text" class="form-control" size="40" maxlength="255">
                </div>&nbsp;&nbsp;
                <div class="form-group">
                    <label for="text">Data Saída</label>
                    <input id="DataSai_Proc" name="DataSai_Proc" type="text" class="form-control" size="10" maxlength="10" onKeyPress="DataHora(event, this)">
                </div>&nbsp;&nbsp;
                <div class="form-group">
                    <label for="text">Destino</label>
                    <select id="Dest_Proc" name="Dest_Proc" class="form-control">
                        <option value="-1">SELECIONE UM TIPO</option>
<?php
while ($row_RsProj = mysql_fetch_assoc($RsProj)) {
    echo "<option value='" . $row_RsProj['Cod_Set'] . "'>" . $row_RsProj['Nome_Set'] . "</option>";
}
?>
                    </select></div><br><br>
                <div class="form-group">
                    <label for="text">Observações</label>
                    <textarea id="Obs_Proc" name="Obs_Proc" cols="80" rows="4"></textarea>
                </div>
                <br><br><br>
                <div class="form-group">
                    <input type="hidden" name="MM_insert" value="form2">
                    <input type="submit" class="form-control" value="| Cadastrar |">
                </div>
            </form>
        </div>
        <script type="text/javascript">
            var spryselect1 = new Spry.Widget.ValidationSelect("spryselect1", {isRequired: false, invalidValue: "-1"});
        </script>
    </body>
</html>
<?php
mysql_free_result($RsVe);
?>