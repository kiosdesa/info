import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { LoadingController } from 'ionic-angular';
import { Observable } from 'rxjs/Observable';
import { Api } from '../api/api';

@Injectable()
export class UploadProvider 
{
   /**
    * @name _READER
    * @type object
    * @private
    * @description Creates a FileReader API object
    */
	 private _READER:any = new FileReader();
	 
   /**
    * @name _REMOTE_URI
    * @type String
    * @private
    * @descriptionThe URI for the remote PHP script that will handle the image upload/parsing
    */
   //private _REMOTE_URI:string 	= "http://YOUR-REMOTE-URI-HERE/parse-upload.php";
   constructor(
		 private api: Api, 
		 public loading: LoadingController, 
		 public http: HttpClient)
   {}

   /**
    * @public
    * @method handleImageSelection
    * @param event  {any} The DOM event that we are capturing from the File input field
    * @description Uses the FileReader API to capture the input field event, retrieve
    * the selected image and return that as a base64 data URL courtesy of
    *	an Observable
    * @return {Observable}
    */
   handleImageSelection(event:any):Observable<any>
   {
      let file: any= event.target.files[0];
      this._READER.readAsDataURL(file);
      return Observable.create((observer) =>
      {
         this._READER.onloadend = () =>
         {
            observer.next(this._READER.result);
            observer.complete();
         }
      });
   }

   /**
    * @public
    * @method isCorrectFile
    * @param file  {String} The file type we want to check
    * @description   	Uses a regular expression to check that the supplied file format
    *  	matches those specified within the method
    * @return {any}
    */
   isCorrectFileType(file)
   {
      return (/^(jpg|jpeg|png)$/i).test(file);
   }

   /**
    * @public
    * @method uploadImageSelection
    * @param file {String}    The file data to be uploaded
    * @param mimeType  	{String}    The file's MimeType (I.e. jpg, gif, png etc)
    * @description Uses the Angular HttpClient to post the data to a remote
    * PHP script, returning the observable to the parent script
    *	allowing that to be able to be subscribed to
    * @return {any}
    */
   uploadImages(root, param: any, name: string, mimeType: string):Observable<any>
   {
			const fileName:any = name + '.' + mimeType
			//const param: any = {name:fileName, file:file};
         if(param) param['name'] = fileName;
         //console.log(param)
					
			let seq = this.api.upload(root, param).share();
			return seq;
	 }
}
