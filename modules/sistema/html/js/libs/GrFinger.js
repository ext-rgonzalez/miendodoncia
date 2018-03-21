function Initialize() {
	try
	{
	GrFingerX.Initialize();
	GrFingerX.CapInitialize();
	}
	catch (e)
	{
	alert(e);
	}
}


function Finalize() {
	GrFingerX.Finalize();	
}
