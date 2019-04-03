import { Component } from '@angular/core';
import { IonicPage, NavController, ViewController, NavParams } from 'ionic-angular';
import { RequestApiProvider, Online } from './../../providers/providers';

interface FormFilterScheme
{
  label: string
  type: string
  name: string
  value: any
}
type FormFieldsInterface = FormFilterScheme[]

@IonicPage()
@Component({
  selector: 'page-product-category-modal',
  templateUrl: 'product-category-modal.html',
})
export class ProductCategoryModalPage 
{
  _dataChoose: any = {};
  _dataParam: any;
  _typeFilterParam: any;
  _dataItems: any;
  _typeDataItems: any;
  _dataFormInterface: FormFieldsInterface;
  title: any;
  section: any;
  
  constructor(
    private api: RequestApiProvider,
    private online: Online,
    public navCtrl: NavController,
    public viewCtrl: ViewController,  
    public navParams: NavParams) 
  {
    this.loadData();
  }

  loadData()
  {
    let dataParam = this.navParams.get('modalParam');
    this._dataParam = dataParam;
    this.title = dataParam.name;
    this.getFilterField(dataParam.section); 
  }

  async getFilterField(endpoint)
  {
    await this.api.get('app/' + endpoint + 'Product', endpoint + 'ProductApp')
    .subscribe((res: any) => {
      let theRes = res[endpoint + 'ProductApp'];
      let theItems = theRes.items;
      this._dataItems = theItems.field;
      this._typeDataItems = theItems.typefield;
      if(this._dataParam.price_min) this._dataChoose.price_min = this._dataParam.price_min;
      if(this._dataParam.price_max) this._dataChoose.price_max = this._dataParam.price_max;
    },
		(err) => {
      this.cancel();
      this.online.checkOnline(false);
    })
  }

  //choose(type, e)
  //{
  //  this._dataChoose[type] = e;
  //}

  cancel() 
  {
    this.viewCtrl.dismiss();
  }

  done() 
  {
    if(this._dataChoose != undefined)
    {
      if(this._dataChoose.price_min == '') this._dataChoose.price_min = null;
      if(this._dataChoose.price_max == '') this._dataChoose.price_max = null;
      const objA = this._dataParam;
      const objB = this._dataChoose;
      const _returnData = {...objA, ...objB, mergefield:true};
      //console.log(_returnData)
      this.viewCtrl.dismiss(_returnData);
    }
  }
}
