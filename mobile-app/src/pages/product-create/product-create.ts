import { Component, ViewChild } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { 
  IonicPage, NavParams, NavController, ViewController, LoadingController, 
  PopoverController, ToastController, ModalController, AlertController, Navbar
} from 'ionic-angular';
import { Storage } from '@ionic/storage';
import { RequestApiProvider, Online } from './../../providers/providers';

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
  selector: 'page-product-create',
  templateUrl: 'product-create.html'
})
export class ProductCreatePage 
{
  @ViewChild(Navbar) navBar: Navbar;

  _paramsRoot: any;
  _cluster = 'account';
  _paramID: any;
  _itemData: any;
  _readWriteAccess: boolean = true;
  _createField: fieldInterface;
  _galleryPush: any = [];
  _cdnServer:string;
  _showUpload: boolean = false;
  _draftMode: boolean = false;

  _translateString:any;

  constructor(
    private storage: Storage, 
    private api: RequestApiProvider,
    private online: Online, 
    private translate: TranslateService, 
    public popoverCtrl: PopoverController, 
    public alert: AlertController, 
    public modalCtrl: ModalController, 
    public navCtrl: NavController, 
    public viewCtrl: ViewController, 
    public toast: ToastController, 
    public loading: LoadingController, 
    public navParam: NavParams
  )
  {
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
      if(this._draftMode == false)
      {
        const name_index = this._createField.findIndex(x => x.field == 'name');
        if(this._createField[name_index].value.length > 4)
        {
          this.createProduct().then(()=>{
            this.navCtrl.pop()
          })
        }
        else
        {
          this.navCtrl.pop()
        }
      }
      else
      {
        this.navCtrl.pop()
      }
    }; 
  }

  async getProductField()
  {
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });
    loader.present();

    const endpoint = 'formsProduct';
    await this.api.post('sensus', endpoint, {init:'product-forms', pack:{fieldForm:{planning:'create'}}})
    .subscribe((res:any)=>{
      const Resp = res[endpoint];
      if(Resp.items.approve == true)
      {
        this._createField = Resp.items.data
        this._galleryPush = Resp.items.photo.value
        this._cdnServer = Resp.items.server
      }
      else
      {
        this.showToast(Resp.items.message, 'warning-toast')
      }
      loader.dismiss();
    },
    (err)=>{
      loader.dismiss();
      this.showToast('Error...', 'danger-toast')
      this.online.checkOnline(true)
    })
  }

  doCreate(param?:any)
  {
    if(this._draftMode == true)
    {
      // Fungsi param untuk mengatasi nilai isian change product jika melakukan upload foto
      param = param ? param : this._createField;
      this.changeProduct({trace:param})
    }
    else
    {
      this.createProduct()
    }
  }

  async createProduct()
  {
    const endpoint = 'addProduct';
    await this.api.post('sensus', endpoint, {init:'product-add', pack:{fieldForm:{trace:this._createField}}})
    .subscribe((res:any)=>{
      const Resp = res[endpoint];
      if(Resp.items.approve == true)
      {
        this._draftMode = true
        this.showToast(Resp.items.message, 'info-toast')
      }
      else
      {
        this._draftMode = false
        this.showToast(Resp.items.message, 'warning-toast')
      }
    },
    (err)=>{
      this._draftMode = false
      this.showToast('Error...', 'danger-toast')
      this.online.checkOnline(true)
    })
  }

  async changeProduct(param)
  {
    const endpoint = 'changeProduct';
    await this.api.post('sensus', endpoint, {init:'product-change', pack:{fieldForm:param}})
    .subscribe((res:any)=>{
      const Resp = res[endpoint];
      let toastColor = 'info-toast'
      if(Resp.items.approve == true)
      {
        toastColor = 'info-toast'
      }
      else
      {
        toastColor = 'warning-toast'
      }
      this.showToast(Resp.items.message, toastColor)
    },
    (err)=>{
      this.showToast('Error...', 'danger-toast')
      this.online.checkOnline(true)
    })
  }

  async doUpdate()
  {
    // Variable konstan untuk _createField
    let _createField: any = this._createField;
    // Menyiapkan push ke _dataForm jika _galleryPush berisi lebih dari 1 foto
    if(this._galleryPush.length > 0)
    {
      _createField.push({field:'photo', value:this._galleryPush})
    }

    this.changeProduct({trace:_createField})
  }

  async uploadFoto(page, param?:any)
  {
    let parameter = param ? {paramRoots:param} : {paramRoots:{}};
    // Menetapkan angka index untuk penamaan file dari jumlah variable _galleryPush
    const index = this._galleryPush.length + 1;
    // Menambahkan suffix 'thumb_' setelah mendapatkan angka index
    const suffix_name = 'thumb_' + index;
    // Mendapatkan nama barang dari input name untuk preffix penamaan fie
    const name_index = this._createField.findIndex(x => x.field == 'name');
    const product_name = this._createField[name_index].value; // Nama barang dari key value
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
          return this.doCreate({photo:currentGallery})
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
      this.doCreate(params)
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
              this.doCreate({thumb:file})
            }
         }
      ]
   });
   alert.present();
  }

  sanitizeInput(val, index)
  {
    const regexSanitizeI = /[^a-zA-Z0-9\,\.\_\-\s\#]/g;
    this._createField[index].value = val.replace(regexSanitizeI, '')
    if(this._createField[index].field == 'name')
    {
      const name_index = this._createField.findIndex(x => x.field == 'name');
      if(this._createField[name_index].value.length > 4)
      {
        this._showUpload = true
      }
      else
      {
        this._showUpload = false
      }
    }
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
    if(this._createField[i].value.length == 4 || approve == true)
    {
      let popover = this.popoverCtrl.create('ChooseCategoryPage', {paramRoots:{query:this._createField[i].value}});
      popover.present({ev:event})

      popover.onDidDismiss(data => {
        if(data)
        {
          const category_index = this._createField.findIndex(x => x.field == 'category');
          this._createField[category_index].value = data.id
          const category_text_index = this._createField.findIndex(x => x.field == 'category_text');
          this._createField[category_text_index].value = data.name
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
