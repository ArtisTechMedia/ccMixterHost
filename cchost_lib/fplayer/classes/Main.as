import flash.external.ExternalInterface;


class Main extends MovieClip 
{
	 var sound:Sound=new Sound();
	 var offset:Number;
	 var isplaying:Boolean;
	 var count:Number=0;
	 var id;
	 var vwidth:Number=100;
	 var intervala=70;
	 var setcallbacks=false;
	 var callbackid;
	 function Main() {
		setCallbacks();	
	 }
	 function setCallbacks()
	 {
		ExternalInterface.addCallback("ccStop", this, Stop); 
		ExternalInterface.addCallback("ccPlaySong", this, Play); 
		ExternalInterface.addCallback("ccPlayPause", this, PlayPause); 
		ExternalInterface.addCallback("ccsetPos", this, setPos); 
		ExternalInterface.addCallback("ccsetVolume", this, setVol); 
	 }
	 function setWidth(width)
	 {
		 vwidth=width;
	 }
	 function setIntervalmm(interv){
		 intervala=interv;
	 }
	 function SetBars(sound:Sound,vwidth)
	 {
		 
		if(sound.getBytesTotal()!=null){
		ExternalInterface.call("setouter",(sound.getBytesLoaded()/sound.getBytesTotal())*vwidth);
		ExternalInterface.call("setpos", (sound.getPosition()/sound.getDuration())*((sound.getBytesLoaded()/sound.getBytesTotal())*vwidth));
		if(sound.getPosition()==sound.getDuration()&&sound.getBytesLoaded()>10000){
			ExternalInterface.call("SongDone");
		}
		}
		
		//ExternalInterface.call("setouter",200);
	 }
	 
	 function setPos(pos)
	 {
         offset=pos/((sound.getBytesLoaded()/sound.getBytesTotal())*100)*(sound.getDuration());
		 if(isplaying){
			 sound.start(offset/1000);
		 }
	 }
	 function setVol(vol)
	 {
		 sound.setVolume(vol);
	 }
	 //function Play()
	 function Play(song)
	 {
		 sound=new Sound();
		 sound.stop();
		 sound.loadSound(song,true);
		 isplaying=true;
		 vwidth=100;
		 if(id!=null) clearInterval(id);
		 id=setInterval(SetBars, intervala,sound,vwidth); 
	
		 
	 }
	 function Stop()
	 {
	   sound.stop();
	   isplaying=false;
	 }
	
	 function PlayPause()
	 {
		 if(isplaying)
		 {
			offset=sound.getPosition();
			sound.stop();
			isplaying=false;
		 }
		 else
		 {
			sound.start(offset/1000);
			isplaying=true;
		 }
		 
	 }
	static function main()
	{
		
		 var test:Main = new Main();
		 	
	//	 Object.registerClass("test", Main);
		 
		 
	}
}
