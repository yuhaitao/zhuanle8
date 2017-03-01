function showdiv(targetid,objN){
    var target=document.getElementById(targetid);
    var clicktext=document.getElementById(objN)
    if (target.style.display=="block"){
        target.style.display="none";
        clicktext.innerText="修改";
    } else {
        target.style.display="block";
        clicktext.innerText='关闭';
    }
}
function turnoff(obj){
    document.getElementById(obj).style.display="none";
}
function turnon(obj){
    document.getElementById(obj).style.display="block";
}