import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, ToastController, LoadingController, PopoverController } from 'ionic-angular';
import { Storage } from '@ionic/storage';
import { RequestApiProvider, Online } from '../../providers/providers'

interface kiosData 
{
  field: string
  value: any
  label: any
  type: any
  readonly?:any
  placeholder?:any
  attribute?:any
  autocomplete?:any
}
type FieldLists = kiosData[]

@IonicPage()
@Component({
  selector: 'page-kiosku-edit',
  templateUrl: 'kiosku-edit.html',
})
export class KioskuEditPage 
{
  _dataKios: FieldLists;
  _paramsRoot: any;
  _cluster = 'account';
  _tmpValue: any;

  constructor(
    private api: RequestApiProvider, 
    private online: Online, 
    private storage: Storage, 
    public param: NavParams, 
    public popoverCtrl: PopoverController, 
    public loading: LoadingController, 
    public navCtrl: NavController, 
    public toast: ToastController, 
    public navParams: NavParams) 
  {
    this._paramsRoot = this.navParams.get('paramRoots')
    this.storage.get('loginToken')
    .then((val) => {
      if(val)
      {
        this.getKiosField()
      }
    })
  }

	async getKiosField()
	{
    let loader = this.loading.create({spinner: 'dots', content: 'Loading...'});
    loader.present();
    const endpoint = 'selfSeller';
    await this.api.post(this._cluster, endpoint, {init:'seller-self', pack:{filedForm:{planning:'edit'}}})
    .subscribe((res:any) => {
      const Resp = res[endpoint]
      if(Resp.items.approve == true)
      {
        this._dataKios = Resp.items.data;
      }
      else
      {
        this.showToast(Resp.items.message, 'danger-toast')
      }
      loader.dismiss()
		},
		(err)=>{
      loader.dismiss()
      this.online.checkOnline(true)
		})
  }
  
  async doUpdate()
  {
    let endpoint = 'modifySeller';
    let loader = this.loading.create({spinner: 'dots', content: 'Loading...'});
    loader.present();
    await this.api
    .post(this._cluster, endpoint, {init:'seller-modify', pack:{fieldForm:{trace:this._dataKios}}})
    .subscribe((res: any) => {
      let Resp = res[endpoint];
      let theItems = Resp.items;
      if(theItems.approve == true)
      {
        this.showToast(Resp.items.message, 'info-toast')
        this.navCtrl.pop()
      }
      else
      {
        this.showToast(Resp.items.message, 'danger-toast')
      }
      loader.dismiss();
    },
    (err) => {
      loader.dismiss();
      this.online.checkOnline(false);
    })
  }

  sanitizeInput(val, index)
  {
    const regexSanitizeI = /[^a-zA-Z0-9\_\-\s]/g;
    const regexSanitizeII = /[^a-zA-Z0-9\,\.\_\-\s]/g;
    const regexSanitize = this._dataKios[index].field == 'slug' || this._dataKios[index].field == 'name' ? regexSanitizeI : regexSanitizeII;
    this._dataKios[index].value = val.replace(regexSanitize, '')
  }

  popoverPress(event, i, f, t)
  {
    if(event.charCode == 13)
    {
      this.choosePopover(event, i, f, t, true)
    }
  }

  choosePopover(event, i, f, t, approve?:any)
  {
    approve = approve ? approve : false;
    if(this._dataKios[i].value.length == 4 || approve == true)
    {
      let popover = this.popoverCtrl.create('CitySearchPage', {paramRoots:{query:this._dataKios[i].value, field:f, temp:t}});
      popover.present({ev:event})

      popover.onDidDismiss(data => {
        if(data)
        {
          if(f != 'postal_code')
          {
            const regexSanitize = /[^a-zA-Z\_\-\s]/g;
            this._dataKios[i].value = data.district_name

            const city_name_index = this._dataKios.findIndex(x => x.field == 'city');
            const province_index = this._dataKios.findIndex(x => x.field == 'province');
            const district_code_index = this._dataKios.findIndex(x => x.field == 'district_code');
            this._dataKios[district_code_index].value = data.district_id
            this._dataKios[city_name_index].value = data.city_name.replace(regexSanitize, '')
            this._dataKios[province_index].value = data.province_name.replace(regexSanitize, '')
          }
          
          const postal_code_index = this._dataKios.findIndex(x => x.field == 'postal_code');
          const defineInjectZipCode = data ? (
            'zip_code' in data ? data.zip_code : undefined
          ) : undefined;
          const zipCodeIsNumber = defineInjectZipCode ? /^-?(0|[1-9]\d*)?$/.test(defineInjectZipCode) : false;
          if(zipCodeIsNumber == true)
          {
            this._dataKios[postal_code_index].value = data.zip_code
            this._dataKios[postal_code_index].type = 'number'
            this._dataKios[postal_code_index].readonly = true
          }
          else
          {
            this._tmpValue = defineInjectZipCode;
            this._dataKios[postal_code_index].value = undefined
            this._dataKios[postal_code_index].type = 'autocomplete'
            this._dataKios[postal_code_index].readonly = false
            //console.log(this._dataKios[postal_code_index])
          }
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
