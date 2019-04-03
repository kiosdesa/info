import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';
import { ProductCreatePage } from './product-create';

@NgModule({
  declarations: [
    ProductCreatePage,
  ],
  imports: [
    IonicPageModule.forChild(ProductCreatePage),
    TranslateModule.forChild()
  ],
  exports: [
    ProductCreatePage
  ]
})
export class ProductCreatePageModule { }
