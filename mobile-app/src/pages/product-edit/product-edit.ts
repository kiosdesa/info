import { Component, ViewChild } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { 
  IonicPage, NavController, NavParams, ViewController, Navbar, 
  ToastController, PopoverController, ModalController, AlertController 
} from 'ionic-angular';
import { Storage } from '@ionic/storage';
import { RequestApiProvider, Online } from '../../providers/providers';

interface fieldForm {
  field: string
  value: any
  label: any
  type: any
  sub_value?: any
  readonly?:any
  placeholder?:any
  attribute?:any
  autocomplete?:any
}
type fieldInterface = fieldForm[]

@IonicPage()
@Component({
  selector: 'page-product-edit',
  templateUrl: 'product-edit.html',
})
export class ProductEditPage 
{
  @ViewChild(Navbar) navBar: Navbar;

  _paramsRoot: any;
  _cluster = 'account';
  _readWriteAccess: boolean = true;
  _dataForms: fieldInterface;
  _dataSlug: string;
  _galleryPush:any = []
  _cdnServer:string;
  _hasSaved: boolean = true;

  _translateString:any;

  constructor(
    private api: RequestApiProvider,
    private online: Online, 
    private storage: Storage, 
    private translate: TranslateService, 
    public navCtrl: NavController, 
    public viewCtrl: ViewController, 
    public toast: ToastController, 
    public alert: AlertController, 
    public popoverCtrl: PopoverController, 
    public modalCtrl: ModalController, 
    public navParam: NavParams
  ){
    this._paramsRoot = this.navParam.get('paramRoots')
    this.translate.get(['REPLACE_BUTTON', 'PRIMARY_IMAGES_TITLE', 'DELETE_BUTTON', 'CONFIRM_TITLE'])
    .subscribe((val)=>{
      this._translateString = val;
    })

    this.storage.get('loginToken')
    .then((val) => {
      if(val)
      {
        this.getProductField()
      }
    })
  }

  ionViewDidLoad()
  {
    this.navBar.backButtonClick = ()=>
    {
      if(this._hasSaved == false)
      {
        this.doUpdate().then(()=>{
          this.navCtrl.pop()
        })
      }
      else
      {
        this.navCtrl.pop()
      }
    }; 
  }

  async getProductField()
  {
    const endpoint = 'formsProduct';
    let fields = {planning:'update'};
    if(this._paramsRoot) if('slug' in this._paramsRoot) fields['slug'] = this._paramsRoot.slug
    await this.api.post('sensus', endpoint, {init:'product-forms', pack:{fieldForm:fields}})
    .subscribe((res:any)=>{
      const Resp = res[endpoint];
      if(Resp.items.approve == true)
      {
        this._dataSlug = Resp.items.slug;
        this._dataForms = Resp.items.data;
        this._cdnServer = Resp.items.server;
        this._galleryPush = Resp.items.photo.value
      }
      else
      {
        this.showToast(Resp.items.message, 'warning-toast')
      }
    },
    (err)=>{
      this.showToast('Error..', 'danger-toast')
      this.online.checkOnline(true)
    })
  }

  async changeProduct(param)
  {
    const endpoint = 'changeProduct';
    await this.api.post('sensus', endpoint, {init:'product-change', pack:{fieldForm:param, id:this._dataSlug}})
    .subscribe((res:any)=>{
      const Resp = res[endpoint];
      let toastColor = 'info-toast'
      if(Resp.items.approve == true)
      {
        this._hasSaved = true
        toastColor = 'info-toast'
      }
      else
      {
        this._hasSaved = false
        toastColor = 'warning-toast'
      }
      this.showToast(Resp.items.message, toastColor, 1500)
    },
    (err:any)=>{
      this._hasSaved = false
      this.showToast(err.message, 'danger-toast')
      this.online.checkOnline(true)
    })
  }

  async doUpdate()
  {
    // Variable konstan untuk _dataForms
    let _dataForms: any = this._dataForms;
    // Menyiapkan push ke _dataForm jika _galleryPush berisi lebih dari 1 foto
    if(this._galleryPush.length > 0)
    {
      _dataForms.push({field:'photo', value:this._galleryPush})
    }

    await this.changeProduct({trace:_dataForms})
  }

  async uploadFoto(page, param?:any)
  {
    let parameter = param ? {paramRoots:param} : {paramRoots:{}};
    // Menetapkan angka index untuk penamaan file dari jumlah variable _galleryPush
    const index = this._galleryPush.length + 1;
    // Menambahkan suffix 'thumb_' setelah mendapatkan angka index
    const suffix_name = 'thumb_' + index;
    // Mendapatkan nama barang dari input name untuk preffix penamaan fie
    const name_index = this._dataForms.findIndex(x => x.field == 'name');
    const product_name = this._dataForms[name_index].value; // Nama barang dari key value
    // Menetapkan nama gambar yang akan di upload
    parameter.paramRoots['name'] = suffix_name + '_' + this._paramsRoot.seller + '_' + product_name
    // Show Modal
    let addModal = this.modalCtrl.create(page, parameter);
    addModal.onDidDismiss(data=>{
      if(data)
      {
        const decisionReplace = param ? 'replace' in param ? true : false : false;
        if(decisionReplace == true)
        {
          if('curentIndex' in param)
          {
            this._galleryPush[param.curentIndex] = data
          }
        }
        else
        {
          this._galleryPush.push(data)
        }
        
        setTimeout(()=>{
          const currentGallery = this._galleryPush;
          return this.changeProduct({photo:currentGallery})
        }, 900)
        //console.log(this._galleryPush)
      }
    })
    addModal.present()
  }

  removeImages(index, file?:any)
  {
    // Memastikan jumlah foto pada varaiable _galleryPush tidak nol/kosong
    if(this._galleryPush.length > 0)
    {
      // Menghapus gambar yang akan di proses di variable _galleryPush
      this._galleryPush.splice(index, 1)
      // Memastikan jumlah foto lebih dari 1 jika hanya satu maka parameter XHTTP tidak akan dicantumkan remove_photo
      const _currentGallery = this._galleryPush;
      let params = {photo:_currentGallery}
      if(file) params['hapus_foto'] = file;
      this.changeProduct(params)
    }
  }
  
  questionImages(index, file)
  {
   let alert = this.alert.create({
      cssClass: 'no-scroll',
      title: this._translateString.CONFIRM_TITLE,
      buttons:[
         {
            text:this._translateString.DELETE_BUTTON,
            handler:()=>{
              this.removeImages(index, file)
            }
         },
         {
            text:this._translateString.REPLACE_BUTTON,
            handler:()=>{
              this.uploadFoto('ProductUploadPage', {replace:file, curentIndex:index})
            }
         },
         {
            text:this._translateString.PRIMARY_IMAGES_TITLE,
            handler:()=>{
              this.changeProduct({thumb:file})
            }
         }
      ]
   });
   alert.present();
  }

  sanitizeInput(val, index)
  {
    const regexSanitizeI = /[^a-zA-Z0-9\,\.\_\-\s\#]/g;
    this._dataForms[index].value = val.replace(regexSanitizeI, '')
    this._hasSaved = false
  }

  popoverPress(event, i)
  {
    if(event.charCode == 13)
    {
      this.choosePopover(event, i, true)
    }
  }

  choosePopover(event, i, approve?:any)
  {
    approve = approve ? approve : false;
    if(this._dataForms[i].value.length == 4 || approve == true)
    {
      let popover = this.popoverCtrl.create('ChooseCategoryPage', {paramRoots:{query:this._dataForms[i].value}});
      popover.present({ev:event})

      popover.onDidDismiss(data => {
        if(data)
        {
          const category_index = this._dataForms.findIndex(x => x.field == 'category');
          this._dataForms[category_index].value = data.id
          const category_text_index = this._dataForms.findIndex(x => x.field == 'category_text');
          this._dataForms[category_text_index].value = data.name
        }
      })
    }
  }

  showToast(message?:string, color?:string, duration?:number)
  {
    const colors = color ? color : '';
    const durations = duration ? duration : 3000;
    let toast = this.toast.create({
      message: message,
      cssClass: colors,
      duration: durations,
      position: 'top'
    });
    toast.present();
  }
}
