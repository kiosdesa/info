import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, LoadingController, ToastController, ViewController } from 'ionic-angular';
import { UploadProvider, Online } from '../../providers/providers'

@IonicPage()
@Component({
  selector: 'page-product-upload',
  templateUrl: 'product-upload.html',
})
export class ProductUploadPage {
  
   _paramRoots: any;
   image:string = undefined;
   isSelected: boolean = false;
   _close:boolean = true;

  private _SUFFIX: string;

  constructor(
   private upload: UploadProvider, 
   private online: Online, 
   public toastCtrl: ToastController, 
   public loading: LoadingController, 
   public view: ViewController, 
   public navCtrl: NavController, 
   public navParams: NavParams)
  {
     this._paramRoots = this.navParams.get('paramRoots')
  }

  selectFileToUpload(event):void
  {
   this.upload
   .handleImageSelection(event)
   .subscribe((res)=>
   {
      // Retrieve the file type from the base64 data URI string
      this._SUFFIX = res.split(':')[1].split('/')[1].split(";")[0];
      if(this.upload.isCorrectFileType(this._SUFFIX))
      {
         this.isSelected = true
         this.image = res;
      }
      else
      {
         this.showToast('Type file allow: jpg, gif or png', 'warning-toast');
      }
   },
   (err)=>
   {
      //console.dir(err);
      this.showToast(err.message , 'danger-toast');
   });
  }

  uploadFile():void
  {
      let loading = this.loading.create({spinner: 'dots', content: 'Loading...'});
      loading.present();
      
      this._close = false;
      let parameter = this._paramRoots ? this._paramRoots : {};
      parameter['file'] = this.image;
      const nameFile = this._paramRoots ? 'name' in this._paramRoots ? this._paramRoots.name : null : null;
      const endpoint = 'productUpload';
      this.upload
      .uploadImages('product', parameter, nameFile, this._SUFFIX)
      .subscribe((res)=>
      {
         const Resp = res[endpoint];
         if(Resp.items.approve == true)
         {
            this.showToast(Resp.items.message, 'info-toast')
            this.view.dismiss(Resp.items.data)
         }
         else
         {
            this._close = true;
            this.showToast(Resp.items.message, 'warning-toast')
         }
         loading.dismiss();
      },
      (err:any)=>
      {
         this._close = true;
         //console.dir(err);
         loading.dismiss();
         this.showToast(err.message, 'danger-toast')
         this.online.checkOnline(true)
      });
  }

  removeImage()
  {
      this.isSelected = false
      this.image = undefined;
      this._close = true;
  }

  cancel()
  {
     if(this._close == true)
     {
      this.view.dismiss()
     }
  }
	 
  showToast(message:string, color?:string, durasi?:number)
  {
    let toast = this.toastCtrl.create({
       message: message,
       duration: durasi ? durasi:3000,
       cssClass: color ? color:'',
       position: 'top'
    });
    toast.present();
 }
}
