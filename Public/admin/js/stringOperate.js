/*
 * �ַ��������࣬��Ҫ�����ַ�������Ӻ�ɾ�������磺���ַ��� "a,b,c"������ַ���"d",����ֱ��ɾ��ĳ���ַ�����
 * ��Ҫ�ṩ�������ַ�����ӵ�ĳ���ַ������м��ö��ŷָ�
 */

/*
<script type="text/javascript">  
//StringOperate.baforeInsert = true;  
//StringOperate.isRepeat= true;  
//StringOperate.isDeleteAll = false;  
    function add(){  
        var val = StringOperate.add($("input[name='inputResult']").val(),$("input[name='inputString']").val());  
        $("input[name='inputResult']").val(val);  
    }  
    function del(){  
        var val = StringOperate.remove($("input[name='inputResult']").val(),$("input[name='inputString']").val());  
        $("input[name='inputResult']").val(val);  
    }  
</script> 
*/
var StringOperate ={
		separator    : "|",//�ַ����ָ���
		baforeInsert : false,//�ַ���׷�ӷ�ʽ��Ĭ��false���ں�����ӣ�true��׷�ӵ�ǰ��
		isRepeat     : false,//׷�ӵ��ַ����Ƿ���ظ����
		isDeleteAll  : true,//ɾ������ƥ����ַ���
		
		//�����ӷָ���
		lInsertSeparator : function(operateString){
			if(operateString.indexOf(this.separator)  == 0)
				return operateString;
			return this.separator + operateString;
		},
		//�ұ���ӷָ���
		rInsertSeparator : function(operateString){
			if(operateString.lastIndexOf(this.separator)  == (operateString.length - this.separator.length))
				return operateString;
			return operateString + this.separator; 
		},
		//ȥ����߷ָ���
		lSeparatorTrim   : function(operateString){
			if(operateString.indexOf(this.separator)  == 0)
				return operateString.substring(1);
			return operateString;
		},
		//ȥ���ұߵķָ���
		rSeparatorTrim   : function(operateString){
			if(operateString.lastIndexOf(this.separator)  == (operateString.length - this.separator.length))
				return operateString.substring(0,operateString.length-1);
			return operateString;
		},
		//׷���ַ�������str�ַ��� ׷�ӵ�operateString��
		add : function(operateString, str){
			if( str  && str != ""){
				if(this.isRepeat){//�ظ�׷��
					if(this.baforeInsert){//׷���ڿ�ͷ
						 return this.rSeparatorTrim(this.lSeparatorTrim(str + this.separator + operateString));
					}
					return this.rSeparatorTrim(this.lSeparatorTrim(operateString + this.separator + str));
				}else{
					//��ͷ�ͽ�β����ӷָ���
					operateString =	this.lInsertSeparator(this.rInsertSeparator(operateString));
					if(operateString.indexOf(this.separator + str + this.separator) == -1){
						if(this.baforeInsert){
							return this.rSeparatorTrim(this.lSeparatorTrim(str + operateString));
						}else{
							return this.rSeparatorTrim(this.lSeparatorTrim(operateString + str));
						}
					}
					return this.rSeparatorTrim(this.lSeparatorTrim(operateString));
				}
			}
		},
		//ɾ��ָ���ַ���
		remove_old : function(operateString, str){
			if(operateString && str && operateString != "" && str != ""){
				//��ͷ�ͽ�β����ӷָ���
				operateString =	this.lInsertSeparator(this.rInsertSeparator(operateString));
				if(this.isDeleteAll){
					var outstr=operateString.split(this.separator);
					removeByValue(outstr, str);
					//alert(outstr);
					operateString = joinByValue(outstr,this.separator);

					//operateString = operateString.replace(new RegExp(this.separator,"g"),this.separator + this.separator);
					//ɾ������ƥ����ַ���
					//operateString =	 operateString.replace(new RegExp(this.separator + str +this.separator,"g"),this.separator);
					//operateString =  operateString.replace(new RegExp(this.separator+"{2,}","g"),this.separator);
				}else{
					operateString =	 operateString.replace(new RegExp(this.separator + str + this.separator),this.separator);
				}
				return this.rSeparatorTrim(this.lSeparatorTrim(operateString));
			}
		},
		//ɾ��ָ���ַ���
		remove : function(operateString, str){
			if(operateString && str && operateString != "" && str != ""){
				var outstr=operateString.split(this.separator);
				removeByValue(outstr, str);
				//console.log(outstr);
				operateString=outstr.join(this.separator);
				return operateString;
			}
		}
		
		
};


function removeByValue(arr, val) {
	for(var i=0; i<arr.length; i++) {
		if(arr[i] == val) {
			arr.splice(i, 1);
			break;
		}
	}
}
//var somearray = ["mon", "tue", "wed", "thur"]
//removeByValue(somearray, "tue");

function joinByValue(arr,separator) {
	var str="";
	for(var i=0; i<arr.length; i++) {
		if(arr[i]!=""){
			if(str==""){
				str=arr[i];
			}else{
				str=separator+arr[i]
			}
		}
	}
	return str;
}